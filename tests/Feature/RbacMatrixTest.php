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
}
