<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Provider;
use App\Models\Role;
use App\Models\TelehealthSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TriageAndTelehealthParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_triage_score_endpoint_returns_priority_reasons(): void
    {
        $role = Role::firstOrCreate(['name' => 'nurse'], ['label' => 'Nurse']);
        $this->seedPermissions($role);
        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/v1/triage/score', [
                'chief_complaint' => 'Chest pain',
                'pain_score' => 9,
                'vitals' => ['spo2' => 92, 'blood_pressure' => 95],
                'symptoms' => ['shortness of breath'],
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.level', 2);
        $response->assertJsonPath('data.score', 2);
        $response->assertJsonPath('data.severity_score', 85);
        $response->assertJsonPath('data.priority_band', 'Yellow');
    }

    public function test_telehealth_session_generates_secure_join_link_without_zoom(): void
    {
        $patient = Patient::create([
            'mrn' => 'MRN-TEST-001-A',
            'first_name' => 'Secure',
            'last_name' => 'Patient',
            'date_of_birth' => '1991-01-15',
            'sex' => 'Male',
            'phone' => '09170000010',
            'email' => 'secure-patient@example.test',
            'verified' => true,
        ]);
        $provider = Provider::create([
            'user_id' => User::factory()->create()->id,
            'display_name' => 'Dr. Secure',
            'active' => true,
        ]);
        $appointmentType = AppointmentType::create([
            'name' => 'Telehealth',
            'default_duration' => 30,
            'telehealth' => true,
        ]);
        $appointment = Appointment::create([
            'appointment_number' => 'APT-TEST-001-A',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'appointment_type_id' => $appointmentType->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'status' => 'CONFIRMED',
        ]);

        $service = app(\App\Services\TelehealthService::class);
        $session = $service->createSession($appointment);

        $this->assertNotNull($session->join_url);
        $this->assertStringContainsString('/telehealth/' . $session->id . '/join', $session->join_url);
        $this->assertNotSame(TelehealthSession::STATUS_NOT_CONFIGURED, $session->status);
    }

    public function test_telehealth_start_endpoint_marks_session_active(): void
    {
        $patient = Patient::create([
            'mrn' => 'MRN-TEST-002',
            'first_name' => 'Start',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-02-01',
            'sex' => 'Female',
            'phone' => '09170000001',
            'email' => 'start-patient@example.test',
            'verified' => true,
        ]);
        $provider = Provider::create([
            'user_id' => User::factory()->create()->id,
            'display_name' => 'Dr. Start',
            'active' => true,
        ]);
        $appointmentType = AppointmentType::create([
            'name' => 'Telehealth',
            'default_duration' => 30,
            'telehealth' => true,
        ]);
        $appointment = Appointment::create([
            'appointment_number' => 'APT-TEST-002',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'appointment_type_id' => $appointmentType->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'status' => 'CONFIRMED',
        ]);
        $session = TelehealthSession::create([
            'appointment_id' => $appointment->id,
            'start_time' => now(),
            'duration' => 30,
            'status' => 'SCHEDULED',
        ]);

        $role = Role::firstOrCreate(['name' => 'doctor'], ['label' => 'Doctor']);
        $this->seedPermissions($role);
        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/v1/telehealth/' . $session->id . '/start');

        $response->assertOk();
        $this->assertSame('ACTIVE', $session->fresh()->status);
    }

    public function test_telehealth_prescription_endpoint_creates_document(): void
    {
        $patient = Patient::create([
            'mrn' => 'MRN-TEST-003',
            'first_name' => 'Prescription',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-03-01',
            'sex' => 'Female',
            'phone' => '09170000002',
            'email' => 'prescription@example.test',
            'verified' => true,
        ]);
        $provider = Provider::create([
            'user_id' => User::factory()->create()->id,
            'display_name' => 'Dr. Rx',
            'active' => true,
        ]);
        $appointmentType = AppointmentType::create([
            'name' => 'Telehealth',
            'default_duration' => 30,
            'telehealth' => true,
        ]);
        $appointment = Appointment::create([
            'appointment_number' => 'APT-TEST-003',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'appointment_type_id' => $appointmentType->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'status' => 'CONFIRMED',
        ]);
        $session = TelehealthSession::create([
            'appointment_id' => $appointment->id,
            'start_time' => now(),
            'duration' => 30,
            'status' => 'ACTIVE',
        ]);

        $role = Role::firstOrCreate(['name' => 'doctor'], ['label' => 'Doctor']);
        $this->seedPermissions($role);
        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/v1/telehealth/' . $session->id . '/prescription', [
                'medications' => ['Amoxicillin 500mg'],
                'notes' => 'Take once daily for 7 days',
            ]);

        $response->assertOk();
        $this->assertDatabaseCount('clinical_documents', 1);
    }

    public function test_telehealth_reminder_endpoint_sends_email(): void
    {
        Mail::fake();

        $patient = Patient::create([
            'mrn' => 'MRN-TEST-004',
            'first_name' => 'Reminder',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-04-01',
            'sex' => 'Male',
            'phone' => '09170000003',
            'email' => 'reminder@example.test',
            'verified' => true,
        ]);
        $provider = Provider::create([
            'user_id' => User::factory()->create()->id,
            'display_name' => 'Dr. Alert',
            'active' => true,
        ]);
        $appointmentType = AppointmentType::create([
            'name' => 'Telehealth',
            'default_duration' => 30,
            'telehealth' => true,
        ]);
        $appointment = Appointment::create([
            'appointment_number' => 'APT-TEST-004',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'appointment_type_id' => $appointmentType->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'status' => 'CONFIRMED',
        ]);
        $session = TelehealthSession::create([
            'appointment_id' => $appointment->id,
            'start_time' => now(),
            'duration' => 30,
            'status' => 'SCHEDULED',
        ]);

        $role = Role::firstOrCreate(['name' => 'doctor'], ['label' => 'Doctor']);
        $this->seedPermissions($role);
        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/v1/telehealth/' . $session->id . '/reminder', [
                'channel' => 'email',
                'message' => 'This is your telehealth reminder.',
            ]);

        $response->assertOk();
        Mail::assertSentCount(1);
    }

    public function test_telehealth_closeout_creates_encounter_summary_and_marks_session_completed(): void
    {
        $patient = Patient::create([
            'mrn' => 'MRN-TEST-005',
            'first_name' => 'Closeout',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-05-01',
            'sex' => 'Female',
            'phone' => '09170000004',
            'email' => 'closeout@example.test',
            'verified' => true,
        ]);
        $provider = Provider::create([
            'user_id' => User::factory()->create()->id,
            'display_name' => 'Dr. Closeout',
            'active' => true,
        ]);
        $appointmentType = AppointmentType::create([
            'name' => 'Telehealth',
            'default_duration' => 30,
            'telehealth' => true,
        ]);
        $appointment = Appointment::create([
            'appointment_number' => 'APT-TEST-005',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'appointment_type_id' => $appointmentType->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'status' => 'CONFIRMED',
        ]);
        $session = TelehealthSession::create([
            'appointment_id' => $appointment->id,
            'start_time' => now(),
            'duration' => 30,
            'status' => 'ACTIVE',
        ]);

        $role = Role::firstOrCreate(['name' => 'doctor'], ['label' => 'Doctor']);
        $this->seedPermissions($role);
        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/v1/telehealth/' . $session->id . '/closeout', [
                'assessment' => 'Symptoms improved with treatment.',
                'plan' => 'Follow up in 7 days if symptoms persist.',
                'discharge_instructions' => 'Rest, hydrate, and continue medication as prescribed.',
                'clinic_note' => 'Patient was seen via telehealth and is improving.',
            ]);

        $response->assertOk();
        $this->assertSame('COMPLETED', $session->fresh()->status);
        $this->assertDatabaseHas('encounters', [
            'appointment_id' => $appointment->id,
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'type' => 'TELEHEALTH',
        ]);
    }

    public function test_telehealth_end_endpoint_marks_session_completed(): void
    {
        $patient = Patient::create([
            'mrn' => 'MRN-TEST-001',
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-01-01',
            'sex' => 'Male',
            'phone' => '09170000000',
            'email' => 'patient@example.test',
            'verified' => true,
        ]);
        $provider = Provider::create([
            'user_id' => User::factory()->create()->id,
            'display_name' => 'Dr. Test',
            'active' => true,
        ]);
        $appointmentType = AppointmentType::create([
            'name' => 'Telehealth',
            'default_duration' => 30,
            'telehealth' => true,
        ]);
        $appointment = Appointment::create([
            'appointment_number' => 'APT-TEST-001',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'appointment_type_id' => $appointmentType->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'status' => 'CONFIRMED',
        ]);
        $session = TelehealthSession::create([
            'appointment_id' => $appointment->id,
            'start_time' => now(),
            'duration' => 30,
            'status' => 'ACTIVE',
        ]);

        $role = Role::firstOrCreate(['name' => 'doctor'], ['label' => 'Doctor']);
        $this->seedPermissions($role);
        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/v1/telehealth/' . $session->id . '/end');

        $response->assertOk();
        $this->assertSame('COMPLETED', $session->fresh()->status);
    }

    protected function seedPermissions(Role $role): void
    {
        $permissions = ['triage-patients', 'start-telehealth', 'join-telehealth', 'view-telehealth'];
        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(['name' => $name], ['label' => ucfirst(str_replace('-', ' ', $name))]);
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }
    }
}
