<?php

namespace Tests\Feature;

use App\Http\Controllers\TriageAssessmentController;
use App\Models\Patient;
use App\Models\PreArrivalProfile;
use App\Models\TriageAssessment;
use App\Models\User;
use App\Services\TriageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class TriageAiEvaluationHookTest extends TestCase
{
    public function test_triage_score_uses_pre_arrival_profile_data_when_available(): void
    {
        $user = User::factory()->create([
            'email' => 'triage.nurse.' . Str::uuid() . '@example.test',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'mrn' => 'MRN-TRIAGE-PRE-' . Str::uuid()->toString(),
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'date_of_birth' => '1987-04-18',
            'sex' => 'Female',
            'verified' => true,
        ]);

        PreArrivalProfile::create([
            'patient_id' => $patient->id,
            'token' => 'pre-arrival-token-' . Str::uuid()->toString(),
            'status' => 'pending',
            'visit_reason' => 'Chest pain and shortness of breath',
            'medical_history' => 'Asthma and previous cardiac issues',
            'current_medications' => 'Albuterol inhaler',
            'allergies' => 'Penicillin',
        ]);

        $result = app(TriageService::class)->score([
            'patient_id' => $patient->id,
            'chief_complaint' => 'Follow-up visit',
            'symptoms' => [],
            'pain_score' => 4,
            'vitals' => [
                'blood_pressure' => '120/80',
                'heart_rate' => 92,
                'respiratory_rate' => 18,
                'temperature' => 36.8,
                'spo2' => 97,
            ],
        ]);

        $this->assertLessThanOrEqual(2, $result['level']);
        $this->assertStringContainsString('pre-arrival', strtolower(implode(' ', $result['reasons'])));
    }

    public function test_triage_score_endpoint_accepts_blood_pressure_strings_for_ai_analysis(): void
    {
        $role = \App\Models\Role::firstOrCreate(['name' => 'nurse'], ['label' => 'Nurse']);
        $permission = \App\Models\Permission::firstOrCreate(['name' => 'triage-patients'], ['label' => 'Triage Patients']);
        $role->permissions()->syncWithoutDetaching([$permission->id]);
        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/v1/triage/score', [
                'chief_complaint' => 'Chest pain',
                'pain_score' => 9,
                'vitals' => ['blood_pressure' => '120/80', 'spo2' => 92],
                'symptoms' => ['shortness of breath'],
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.priority', 'Urgent');
        $response->assertJsonPath('data.severity_score', 85);
        $this->assertNotEmpty($response->json('data.reasons'));
    }

    public function test_walk_in_triage_without_pre_arrival_profile_uses_standard_rule_based_scoring(): void
    {
        $user = User::factory()->create([
            'email' => 'triage.walkin.' . Str::uuid() . '@example.test',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'mrn' => 'MRN-TRIAGE-WALKIN-' . Str::uuid()->toString(),
            'first_name' => 'Luis',
            'last_name' => 'Rodriguez',
            'date_of_birth' => '1990-06-12',
            'sex' => 'Male',
            'verified' => true,
        ]);

        $result = app(TriageService::class)->score([
            'patient_id' => $patient->id,
            'chief_complaint' => 'Follow-up visit',
            'symptoms' => [],
            'pain_score' => 1,
            'vitals' => [
                'blood_pressure' => '118/76',
                'heart_rate' => 70,
                'respiratory_rate' => 16,
                'temperature' => 36.6,
                'spo2' => 99,
            ],
        ]);

        $this->assertSame(4, $result['level']);
        $this->assertSame('Non-Urgent', $result['priority']);
    }

    public function test_triage_assessment_requires_explicit_confirmation_before_finalizing(): void
    {
        $nurse = User::factory()->create([
            'email' => 'triage.confirm.' . Str::uuid() . '@example.test',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
        ]);

        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'nurse'],
            ['label' => 'Nurse']
        );
        $permission = \App\Models\Permission::firstOrCreate(
            ['name' => 'triage-patients'],
            ['label' => 'Triage Patients']
        );
        $role->permissions()->syncWithoutDetaching([$permission->id]);
        $nurse->roles()->syncWithoutDetaching([$role->id]);
        $this->actingAs($nurse);

        $patient = Patient::create([
            'user_id' => $nurse->id,
            'mrn' => 'MRN-TRIAGE-CONFIRM-' . Str::uuid()->toString(),
            'first_name' => 'Alicia',
            'last_name' => 'Mendoza',
            'date_of_birth' => '1982-09-22',
            'sex' => 'Female',
            'verified' => true,
        ]);

        $startCount = \App\Models\TriageAssessment::count();

        $response = $this->from(route('triage.create'))->post(route('triage.store'), [
            'patient_id' => $patient->id,
            'chief_complaint' => 'Severe abdominal pain',
            'symptoms' => 'abdominal pain, vomiting',
            'pain_score' => 8,
            'blood_pressure' => '98/62',
            'heart_rate' => 112,
            'respiratory_rate' => 24,
            'temperature' => 38.2,
            'spo2' => 96,
            'notes' => 'Initial note',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('ai_confirmed');
        $this->assertSame($startCount, \App\Models\TriageAssessment::count());
    }

    public function test_triage_assessment_creates_linked_er_visit_when_saving(): void
    {
        $nurse = User::factory()->create([
            'email' => 'triage.linked.visit.' . Str::uuid() . '@example.test',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
        ]);

        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'nurse'],
            ['label' => 'Nurse']
        );
        $permission = \App\Models\Permission::firstOrCreate(
            ['name' => 'triage-patients'],
            ['label' => 'Triage Patients']
        );
        $role->permissions()->syncWithoutDetaching([$permission->id]);
        $nurse->roles()->syncWithoutDetaching([$role->id]);
        $this->actingAs($nurse);

        $patient = Patient::create([
            'user_id' => $nurse->id,
            'mrn' => 'MRN-TRIAGE-LINKED-' . Str::uuid()->toString(),
            'first_name' => 'Rosa',
            'last_name' => 'Dela Cruz',
            'date_of_birth' => '1996-07-18',
            'sex' => 'Female',
            'verified' => true,
        ]);

        $response = $this->from(route('triage.create'))->post(route('triage.store'), [
            'patient_id' => $patient->id,
            'chief_complaint' => 'Chest pain',
            'symptoms' => 'chest pain, shortness of breath',
            'pain_score' => 9,
            'blood_pressure' => '120/80',
            'heart_rate' => 104,
            'respiratory_rate' => 22,
            'temperature' => 37.2,
            'spo2' => 95,
            'notes' => 'the patient is having a chest pain.',
            'ai_confirmed' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('er_visits', ['patient_id' => $patient->id]);
        $this->assertDatabaseHas('triage_assessments', ['patient_id' => $patient->id]);
        $this->assertNotNull(TriageAssessment::where('patient_id', $patient->id)->first()->er_visit_id ?? null);
    }

    public function test_emergency_inline_triage_requires_explicit_confirmation_before_finalizing(): void
    {
        $nurse = User::factory()->create([
            'email' => 'er.triage.confirm.' . Str::uuid() . '@example.test',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
        ]);

        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'nurse'],
            ['label' => 'Nurse']
        );
        $triagePermission = \App\Models\Permission::firstOrCreate(
            ['name' => 'triage-patients'],
            ['label' => 'Triage Patients']
        );
        $erPermission = \App\Models\Permission::firstOrCreate(
            ['name' => 'view-er'],
            ['label' => 'View ER']
        );
        $role->permissions()->syncWithoutDetaching([$triagePermission->id, $erPermission->id]);
        $nurse->roles()->syncWithoutDetaching([$role->id]);
        $this->actingAs($nurse);

        $patient = Patient::create([
            'user_id' => $nurse->id,
            'mrn' => 'MRN-ER-TRIAGE-CONFIRM-' . Str::uuid()->toString(),
            'first_name' => 'Nora',
            'last_name' => 'Rivera',
            'date_of_birth' => '1988-11-05',
            'sex' => 'Female',
            'verified' => true,
        ]);

        $visit = \App\Models\ErVisit::create([
            'visit_number' => 'ER-TEST-' . Str::uuid()->toString(),
            'patient_id' => $patient->id,
            'arrived_at' => now(),
            'arrival_method' => 'Walk-in',
            'chief_complaint' => 'Chest pain',
            'status' => 'ARRIVED',
            'created_by' => $nurse->id,
        ]);

        $startCount = \App\Models\TriageAssessment::count();

        $response = $this->from(route('emergency.show', $visit))->post(route('emergency.triage', $visit), [
            'chief_complaint' => 'Chest pain',
            'pain_score' => 8,
            'priority' => 'Level 2',
            'notes' => 'Initial note',
            'treatment_area' => 'Triage bay',
            'vitals_blood_pressure' => '110/70',
            'vitals_heart_rate' => 110,
            'vitals_respiratory_rate' => 20,
            'vitals_temperature' => 37.1,
            'vitals_spo2' => 97,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('ai_confirmed');
        $this->assertSame($startCount, \App\Models\TriageAssessment::count());
    }
}
