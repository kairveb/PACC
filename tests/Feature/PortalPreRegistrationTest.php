<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\HimsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalPreRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(HimsSeeder::class);
    }

    public function test_patient_can_create_pre_arrival_profile_and_view_ticket(): void
    {
        $role = Role::where('name', 'patient')->firstOrFail();
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->roles()->syncWithoutDetaching([$role->id]);
        $this->assertTrue($user->fresh()->hasRole('patient'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        $patient = Patient::create([
            'user_id' => $user->id,
            'mrn' => 'MRN-PRE-001',
            'first_name' => 'Jane',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-05-15',
            'sex' => 'Female',
            'phone' => '09170000011',
            'email' => 'jane.patient@example.test',
            'verified' => true,
        ]);

        $getResponse = $this->actingAs($user, 'web')->get('/portal/pre-register');
        $getResponse->assertOk();
        $getResponse->assertSee('Pre-registration');

        $postResponse = $this->actingAs($user, 'web')->post('/portal/pre-register', [
            'visit_reason' => 'Follow-up for recurring abdominal pain',
            'initial_notes' => 'Patient reports worsening pain over the last two days.',
            'medical_history' => 'Asthma, no major surgeries',
            'current_medications' => 'Albuterol PRN',
            'allergies' => 'Penicillin',
            'emergency_contact_name' => 'John Patient',
            'emergency_contact_phone' => '09170000099',
            'emergency_contact_relationship' => 'Spouse',
            'address_line1' => '123 Sample Street',
            'address_city' => 'Quezon City',
            'address_province' => 'Metro Manila',
            'address_postal_code' => '1100',
            'contact_phone' => '09170000011',
            'contact_email' => 'jane.patient@example.test',
        ]);

        $postResponse->assertRedirect(route('patients.portal'));
        $this->assertDatabaseHas('pre_arrival_profiles', [
            'patient_id' => $patient->id,
            'status' => 'pending',
            'visit_reason' => 'Follow-up for recurring abdominal pain',
        ]);

        $profile = $patient->preArrivalProfiles()->latest()->first();
        $this->assertNotNull($profile);
        $this->assertNotEmpty($profile->token);
        $this->assertNotEmpty($profile->qr_code_url);

        $dashboardResponse = $this->actingAs($user, 'web')->get(route('patients.portal'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Pre-arrival ticket');
    }

    public function test_patient_cannot_access_staff_telehealth_index(): void
    {
        $role = Role::where('name', 'patient')->firstOrFail();
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->roles()->syncWithoutDetaching([$role->id]);

        Patient::create([
            'user_id' => $user->id,
            'mrn' => 'MRN-ACCESS-001',
            'first_name' => 'Access',
            'last_name' => 'Patient',
            'date_of_birth' => '1991-02-10',
            'sex' => 'Female',
            'phone' => '09170000088',
            'email' => 'access.patient@example.test',
            'verified' => true,
        ]);

        $response = $this->actingAs($user, 'web')->get('/telehealth');

        $response->assertForbidden();
    }

    public function test_patient_dashboard_hides_staff_quick_actions_and_shows_pre_registration_link(): void
    {
        $role = Role::where('name', 'patient')->firstOrFail();
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->roles()->syncWithoutDetaching([$role->id]);

        Patient::create([
            'user_id' => $user->id,
            'mrn' => 'MRN-ACCESS-002',
            'first_name' => 'Portal',
            'last_name' => 'Patient',
            'date_of_birth' => '1988-03-15',
            'sex' => 'Male',
            'phone' => '09170000089',
            'email' => 'portal.patient@example.test',
            'verified' => true,
        ]);

        $dashboard = $this->actingAs($user, 'web')->get('/dashboard');
        $dashboard->assertDontSee('Register Patient');
        $dashboard->assertDontSee('ER Queue');
        $dashboard->assertSee('Pre-register for your visit');

        $portal = $this->actingAs($user, 'web')->get('/patient-portal');
        $portal->assertSee('Pre-register for your visit');
    }
}
