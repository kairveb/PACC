<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\HimsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_expected_role_permission_matrix_is_seeded(): void
    {
        $this->seed(HimsSeeder::class);

        $superAdmin = Role::where('name', 'super-admin')->first();
        $hospitalAdmin = Role::where('name', 'hospital-admin')->first();
        $registration = Role::where('name', 'registration')->first();
        $doctor = Role::where('name', 'doctor')->first();
        $nurse = Role::where('name', 'nurse')->first();
        $patient = Role::where('name', 'patient')->first();

        $this->assertNotNull($superAdmin);
        $this->assertNotNull($hospitalAdmin);
        $this->assertNotNull($registration);
        $this->assertNotNull($doctor);
        $this->assertNotNull($nurse);
        $this->assertNotNull($patient);

        $this->assertSame('Registration / Front Desk', $registration->label);
        $this->assertSame('Super Admin', $superAdmin->label);

        $this->assertTrue($superAdmin->permissions()->where('name', 'manage-users')->exists());
        $this->assertTrue($hospitalAdmin->permissions()->where('name', 'view-audit-logs')->exists());
        $this->assertTrue($registration->permissions()->where('name', 'create-patients')->exists());
        $this->assertTrue($doctor->permissions()->where('name', 'view-triage')->exists());
        $this->assertTrue($nurse->permissions()->where('name', 'manage-beds')->exists());
        $this->assertTrue($patient->permissions()->where('name', 'view-own-appointments')->exists());

        $this->assertTrue($superAdmin->permissions()->where('name', 'view-wards')->exists());
        $this->assertTrue($nurse->permissions()->where('name', 'transfer-patients')->exists());
        $this->assertTrue($nurse->permissions()->where('name', 'discharge-patients')->exists());
        $this->assertTrue($doctor->permissions()->where('name', 'create-admissions')->exists());
        $this->assertTrue($patient->permissions()->where('name', 'view-own-medical-history')->exists());
        $this->assertTrue($patient->permissions()->where('name', 'view-own-telehealth')->exists());

        $this->assertFalse($patient->permissions()->where('name', 'manage-users')->exists());
        $this->assertFalse($registration->permissions()->where('name', 'manage-beds')->exists());
    }

    public function test_dashboard_content_is_role_specific(): void
    {
        $this->seed(HimsSeeder::class);

        $doctor = User::whereHas('roles', fn ($query) => $query->where('name', 'doctor'))->firstOrFail();
        $nurse = User::whereHas('roles', fn ($query) => $query->where('name', 'nurse'))->firstOrFail();
        $patient = User::whereHas('roles', fn ($query) => $query->where('name', 'patient'))->firstOrFail();
        $frontDesk = User::whereHas('roles', fn ($query) => $query->where('name', 'registration'))->firstOrFail();

        $doctorResponse = $this->actingAs($doctor, 'web')->get('/dashboard');
        $doctorResponse->assertOk();
        $doctorResponse->assertSee('My schedule');
        $doctorResponse->assertDontSee('Patient registration queue');

        $nurseResponse = $this->actingAs($nurse, 'web')->get('/dashboard');
        $nurseResponse->assertOk();
        $nurseResponse->assertSee('Triage queue');
        $nurseResponse->assertDontSee('My schedule');

        $patientResponse = $this->actingAs($patient, 'web')->get('/dashboard');
        $patientResponse->assertOk();
        $patientResponse->assertSee('My appointments');
        $patientResponse->assertDontSee('Operations overview');

        $frontDeskResponse = $this->actingAs($frontDesk, 'web')->get('/dashboard');
        $frontDeskResponse->assertOk();
        $frontDeskResponse->assertSee('Registration desk');
        $frontDeskResponse->assertDontSee('Bed occupancy');
    }

    public function test_doctor_and_nurse_see_only_role_scoped_data(): void
    {
        $this->seed(HimsSeeder::class);

        $doctor = User::whereHas('roles', fn ($query) => $query->where('name', 'doctor'))->firstOrFail();
        $nurse = User::whereHas('roles', fn ($query) => $query->where('name', 'nurse'))->firstOrFail();

        $doctorProvider = $doctor->provider()->firstOrFail();
        $otherProvider = \App\Models\Provider::create([
            'user_id' => User::factory()->create()->id,
            'display_name' => 'Dr. Other',
            'active' => true,
        ]);

        $doctorPatient = \App\Models\Patient::create([
            'mrn' => 'MRN-ROLE-001',
            'first_name' => 'Doctor',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-01-01',
            'sex' => 'Female',
            'phone' => '09170000001',
            'email' => 'doctor.patient@example.test',
            'verified' => true,
        ]);

        $otherPatient = \App\Models\Patient::create([
            'mrn' => 'MRN-ROLE-002',
            'first_name' => 'Other',
            'last_name' => 'Patient',
            'date_of_birth' => '1988-02-02',
            'sex' => 'Male',
            'phone' => '09170000002',
            'email' => 'other.patient@example.test',
            'verified' => true,
        ]);

        \App\Models\Encounter::create([
            'encounter_number' => 'ENC-ROLE-001',
            'patient_id' => $doctorPatient->id,
            'provider_id' => $doctorProvider->id,
            'type' => \App\Models\Encounter::TYPE_OUTPATIENT,
            'started_at' => now(),
            'status' => 'ACTIVE',
        ]);

        \App\Models\Encounter::create([
            'encounter_number' => 'ENC-ROLE-002',
            'patient_id' => $otherPatient->id,
            'provider_id' => $otherProvider->id,
            'type' => \App\Models\Encounter::TYPE_OUTPATIENT,
            'started_at' => now(),
            'status' => 'ACTIVE',
        ]);

        $doctorResponse = $this->actingAs($doctor, 'web')->get('/encounters');
        $doctorResponse->assertOk();
        $doctorResponse->assertSee($doctorPatient->full_name);
        $doctorResponse->assertDontSee($otherPatient->full_name);

        $erQueueOne = \App\Models\ErVisit::create([
            'visit_number' => 'ER-ROLE-001',
            'patient_id' => $doctorPatient->id,
            'chief_complaint' => 'Chest pain',
            'arrived_at' => now(),
            'status' => 'ARRIVED',
        ]);

        $erQueueTwo = \App\Models\ErVisit::create([
            'visit_number' => 'ER-ROLE-002',
            'patient_id' => $otherPatient->id,
            'chief_complaint' => 'Back pain',
            'arrived_at' => now(),
            'status' => 'ARRIVED',
        ]);

        \App\Models\ErQueue::create([
            'er_visit_id' => $erQueueOne->id,
            'priority' => 'Level 1',
            'status' => 'WAITING',
            'queued_at' => now(),
        ]);

        \App\Models\ErQueue::create([
            'er_visit_id' => $erQueueTwo->id,
            'priority' => 'Level 2',
            'status' => 'WAITING',
            'queued_at' => now(),
        ]);

        $nurseResponse = $this->actingAs($nurse, 'web')->get('/emergency');
        $nurseResponse->assertOk();
        $nurseResponse->assertSee('Chest pain');
        $nurseResponse->assertSee('Back pain');
    }
}
