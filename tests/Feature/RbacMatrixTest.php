<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\HimsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_has_expected_access_scope(): void
    {
        $this->seed(HimsSeeder::class);

        $superAdmin = User::whereHas('roles', fn ($query) => $query->where('name', 'super-admin'))->firstOrFail();
        $hospitalAdmin = User::whereHas('roles', fn ($query) => $query->where('name', 'hospital-admin'))->firstOrFail();
        $registration = User::whereHas('roles', fn ($query) => $query->where('name', 'registration'))->firstOrFail();
        $doctor = User::whereHas('roles', fn ($query) => $query->where('name', 'doctor'))->firstOrFail();
        $nurse = User::whereHas('roles', fn ($query) => $query->where('name', 'nurse'))->firstOrFail();
        $patient = User::whereHas('roles', fn ($query) => $query->where('name', 'patient'))->firstOrFail();

        $this->actingAs($superAdmin, 'web')->get('/patients/lookup')->assertOk();
        $this->actingAs($superAdmin, 'web')->get('/audit-logs')->assertOk();
        $this->actingAs($superAdmin, 'web')->get('/patients/profile')->assertOk();

        $this->actingAs($hospitalAdmin, 'web')->get('/patients/lookup')->assertOk();
        $this->actingAs($hospitalAdmin, 'web')->get('/audit-logs')->assertOk();
        $this->actingAs($hospitalAdmin, 'web')->get('/patients/profile')->assertForbidden();

        $this->actingAs($registration, 'web')->get('/patients/lookup')->assertOk();
        $this->actingAs($registration, 'web')->get('/patients')->assertOk();
        $this->actingAs($registration, 'web')->get('/audit-logs')->assertForbidden();

        $this->actingAs($doctor, 'web')->get('/encounters')->assertOk();
        $this->actingAs($doctor, 'web')->get('/doctors/queue')->assertOk();
        $this->actingAs($doctor, 'web')->get('/patients/lookup')->assertForbidden();

        $this->actingAs($nurse, 'web')->get('/emergency')->assertOk();
        $this->actingAs($nurse, 'web')->get('/triage')->assertOk();
        $this->actingAs($nurse, 'web')->get('/reports')->assertForbidden();
        $this->actingAs($nurse, 'web')->get('/audit-logs')->assertForbidden();
        $this->actingAs($nurse, 'web')->get('/patients/lookup')->assertForbidden();

        $this->actingAs($doctor, 'web')->get('/reports')->assertOk();
        $this->actingAs($doctor, 'web')->get('/audit-logs')->assertForbidden();
        $this->actingAs($doctor, 'web')->get('/triage')->assertOk();

        $this->actingAs($hospitalAdmin, 'web')->get('/reports')->assertOk();
        $this->actingAs($hospitalAdmin, 'web')->get('/audit-logs')->assertOk();
        $this->actingAs($hospitalAdmin, 'web')->get('/triage')->assertOk();

        $this->actingAs($registration, 'web')->get('/reports')->assertForbidden();
        $this->actingAs($registration, 'web')->get('/audit-logs')->assertForbidden();
        $this->actingAs($registration, 'web')->get('/triage')->assertForbidden();

        $this->actingAs($patient, 'web')->get('/patients/profile')->assertOk();
        $this->actingAs($patient, 'web')->get('/patient-portal')->assertOk();
        $this->actingAs($patient, 'web')->get('/patients/lookup')->assertForbidden();
        $this->actingAs($patient, 'web')->get('/triage')->assertForbidden();
        $this->actingAs($patient, 'web')->get('/reports')->assertForbidden();
        $this->actingAs($patient, 'web')->get('/audit-logs')->assertForbidden();
    }

    public function test_sidebar_menu_respects_role_access_matrix(): void
    {
        $this->seed(HimsSeeder::class);

        $roles = [
            'super-admin' => ['show' => ['Operations', 'Reports', 'Audit Logs'], 'hide' => []],
            'hospital-admin' => ['show' => ['Operations', 'Reports', 'Audit Logs'], 'hide' => []],
            'doctor' => ['show' => ['Operations', 'Reports'], 'hide' => ['Audit Logs']],
            'nurse' => ['show' => [], 'hide' => ['Operations', 'Reports', 'Audit Logs']],
            'registration' => ['show' => [], 'hide' => ['Operations', 'Reports', 'Audit Logs']],
            'patient' => ['show' => [], 'hide' => ['Operations', 'Reports', 'Audit Logs']],
        ];

        foreach ($roles as $roleName => $expectations) {
            $user = User::whereHas('roles', fn ($query) => $query->where('name', $roleName))->firstOrFail();
            $response = $this->actingAs($user, 'web')->get('/dashboard');
            $response->assertOk();

            foreach ($expectations['show'] as $text) {
                $response->assertSee($text);
            }

            foreach ($expectations['hide'] as $text) {
                $response->assertDontSee($text);
            }
        }
    }

    public function test_audit_logs_can_filter_by_action(): void
    {
        $this->seed(HimsSeeder::class);

        $admin = User::whereHas('roles', fn ($query) => $query->where('name', 'hospital-admin'))->firstOrFail();

        \App\Models\AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'login',
            'resource_type' => 'auth',
            'resource_id' => $admin->id,
            'result' => 'success',
            'ip_address' => '127.0.0.1',
            'metadata' => ['role' => 'hospital-admin'],
        ]);
        \App\Models\AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'logout',
            'resource_type' => 'auth',
            'resource_id' => $admin->id,
            'result' => 'success',
            'ip_address' => '127.0.0.1',
            'metadata' => ['role' => 'hospital-admin'],
        ]);

        $response = $this->actingAs($admin, 'web')->get('/audit-logs?action=login');

        $response->assertOk();
        $response->assertViewHas('logs', function ($logs) {
            return $logs->count() === 1
                && $logs->first()->action === 'login'
                && $logs->pluck('action')->filter(fn ($action) => $action === 'logout')->isEmpty();
        });
    }

    public function test_pre_registration_route_respects_patient_only_access_with_super_admin_bypass(): void
    {
        $this->seed(HimsSeeder::class);

        $patientUser = User::whereHas('roles', fn ($query) => $query->where('name', 'patient'))->firstOrFail();
        $patientUser->forceFill(['email_verified_at' => now()])->save();

        Patient::firstOrCreate(
            ['user_id' => $patientUser->id],
            [
                'mrn' => 'MRN-RBAC-PATIENT',
                'first_name' => 'Rbac',
                'last_name' => 'Patient',
                'date_of_birth' => '1990-01-01',
                'sex' => 'Female',
                'phone' => '09170000077',
                'email' => 'rbac.patient@example.test',
                'verified' => true,
            ]
        );

        $this->actingAs($patientUser, 'web')->get('/portal/pre-register')->assertOk();
        $this->actingAs($patientUser, 'web')->post('/portal/pre-register', [
            'visit_reason' => 'Follow-up consultation',
            'initial_notes' => 'Routine review',
            'medical_history' => 'No major concerns',
            'current_medications' => 'None',
            'allergies' => 'None',
            'emergency_contact_name' => 'Jane Contact',
            'emergency_contact_phone' => '09170000099',
            'emergency_contact_relationship' => 'Spouse',
            'address_line1' => '123 Sample Street',
            'address_city' => 'Quezon City',
            'address_province' => 'Metro Manila',
            'address_postal_code' => '1100',
            'contact_phone' => '09170000077',
            'contact_email' => 'rbac.patient@example.test',
        ])->assertRedirect(route('patients.portal'));

        foreach (['registration', 'nurse', 'doctor', 'hospital-admin'] as $roleName) {
            $user = User::whereHas('roles', fn ($query) => $query->where('name', $roleName))->firstOrFail();

            $this->actingAs($user, 'web')->get('/portal/pre-register')->assertForbidden();
            $this->actingAs($user, 'web')->post('/portal/pre-register', [
                'visit_reason' => 'Should not be allowed',
            ])->assertForbidden();
        }

        $superAdmin = User::whereHas('roles', fn ($query) => $query->where('name', 'super-admin'))->firstOrFail();
        $superAdmin->forceFill(['email_verified_at' => now()])->save();

        Patient::firstOrCreate(
            ['user_id' => $superAdmin->id],
            [
                'mrn' => 'MRN-RBAC-SUPER',
                'first_name' => 'Rbac',
                'last_name' => 'Admin',
                'date_of_birth' => '1988-02-02',
                'sex' => 'Male',
                'phone' => '09170000088',
                'email' => 'rbac.superadmin@example.test',
                'verified' => true,
            ]
        );

        $this->actingAs($superAdmin, 'web')->get('/portal/pre-register')->assertOk();
        $this->actingAs($superAdmin, 'web')->post('/portal/pre-register', [
            'visit_reason' => 'Super-admin pre-registration',
            'initial_notes' => 'Bypass test',
            'medical_history' => 'No issues',
            'current_medications' => 'None',
            'allergies' => 'None',
            'emergency_contact_name' => 'Admin Contact',
            'emergency_contact_phone' => '09170000098',
            'emergency_contact_relationship' => 'Sibling',
            'address_line1' => '5 Admin Street',
            'address_city' => 'Manila',
            'address_province' => 'Metro Manila',
            'address_postal_code' => '1000',
            'contact_phone' => '09170000088',
            'contact_email' => 'rbac.superadmin@example.test',
        ])->assertRedirect(route('patients.portal'));
    }

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

    public function test_inpatient_sidebar_is_hidden_when_no_inpatient_children_are_visible_for_the_role(): void
    {
        $this->seed(HimsSeeder::class);

        $roles = [
            'doctor' => ['show' => false, 'items' => ['Bed Board', 'Admissions']],
            'nurse' => ['show' => true, 'items' => ['Bed Board', 'Admissions']],
            'registration' => ['show' => false, 'items' => ['Bed Board', 'Admissions']],
            'patient' => ['show' => false, 'items' => ['Bed Board', 'Admissions']],
            'hospital-admin' => ['show' => true, 'items' => ['Bed Board', 'Admissions']],
            'super-admin' => ['show' => true, 'items' => ['Bed Board', 'Admissions']],
        ];

        foreach ($roles as $roleName => $expectations) {
            $user = User::whereHas('roles', fn ($query) => $query->where('name', $roleName))->firstOrFail();
            $response = $this->actingAs($user, 'web')->get('/dashboard');

            $response->assertOk();

            if ($expectations['show']) {
                $response->assertSee('Inpatient');
                foreach ($expectations['items'] as $item) {
                    $response->assertSee($item);
                }
            } else {
                $response->assertDontSee('Inpatient');
                foreach ($expectations['items'] as $item) {
                    $response->assertDontSee($item);
                }
            }
        }
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
