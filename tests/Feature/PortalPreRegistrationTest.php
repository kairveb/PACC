<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PreArrivalProfile;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\HimsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
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

        $postResponse = $this->withSession(['_token' => 'test-token'])
            ->actingAs($user, 'web')
            ->post('/portal/pre-register', [
                '_token' => 'test-token',
                'first_name' => 'Jane',
                'last_name' => 'Patient',
                'date_of_birth' => '1990-05-15',
                'sex' => 'Female',
                'phone' => '09170000011',
                'email' => 'jane.patient@example.test',
                'visit_reason' => 'Follow-up for recurring abdominal pain',
                'initial_notes' => 'Patient reports worsening pain over the last two days.',
                'medical_history' => 'Asthma, no major surgeries',
                'current_medications' => 'Albuterol PRN',
                'allergies' => 'Penicillin',
                'emergency_name' => 'John Patient',
                'emergency_phone' => '09170000099',
                'emergency_relationship' => 'Spouse',
                'address_line1' => '123 Sample Street',
                'address_city' => 'Quezon City',
                'address_province' => 'Metro Manila',
                'address_postal' => '1100',
            ]);

        $profile = $patient->preArrivalProfiles()->latest()->first();
        $this->assertNotNull($profile);
        $this->assertNotEmpty($profile->reference_code);
        $this->assertMatchesRegularExpression('/^PAC-\d{4}$/', $profile->reference_code);

        $postResponse->assertRedirect(route('patients.portal'));

        $profileRow = DB::table('pre_arrival_profiles')->where('id', $profile->id)->first();
        $this->assertSame($patient->id, $profileRow->patient_id);
        $this->assertSame('pending', $profileRow->status);
        $this->assertNotSame('Follow-up for recurring abdominal pain', $profileRow->visit_reason);
        $this->assertSame('Follow-up for recurring abdominal pain', $profile->fresh()->visit_reason);

        $this->assertNotEmpty($profile->token);
        $this->assertNotEmpty($profile->qr_code_url);

        $dashboardResponse = $this->actingAs($user, 'web')->get(route('patients.portal'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Pre-arrival ticket');
        $dashboardResponse->assertSee($profile->reference_code);
    }

    public function test_patient_pre_registration_accepts_missing_visit_reason_for_dashboard_form(): void
    {
        $role = Role::where('name', 'patient')->firstOrFail();
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'mrn' => 'MRN-PRE-DASH-001',
            'first_name' => 'Jane',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-05-15',
            'sex' => 'Female',
            'phone' => '09170000011',
            'email' => 'jane.patient@example.test',
            'verified' => true,
        ]);

        $response = $this->withSession(['_token' => 'test-token'])
            ->actingAs($user, 'web')
            ->post('/portal/pre-register', [
                '_token' => 'test-token',
                'first_name' => 'Jane',
                'last_name' => 'Patient',
                'date_of_birth' => '1990-05-15',
                'sex' => 'Female',
                'phone' => '09170000011',
                'email' => 'jane.patient@example.test',
                'address_line1' => '123 Sample Street',
                'address_city' => 'Quezon City',
                'address_province' => 'Metro Manila',
                'address_postal' => '1100',
                'emergency_name' => 'John Patient',
                'emergency_phone' => '09170000099',
                'emergency_relationship' => 'Spouse',
            ]);

        $response->assertRedirect(route('patients.portal'));

        $profile = $patient->preArrivalProfiles()->latest()->first();
        $this->assertNotNull($profile);
        $this->assertNull($profile->visit_reason);
    }

    public function test_patient_pre_registration_stores_the_same_demographic_and_contact_fields_as_walk_in_registration(): void
    {
        $role = Role::where('name', 'patient')->firstOrFail();
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'mrn' => 'MRN-PRE-PAIR-001',
            'first_name' => 'Jane',
            'middle_name' => 'M.',
            'last_name' => 'Patient',
            'suffix' => 'Jr.',
            'date_of_birth' => '1990-05-15',
            'sex' => 'Female',
            'civil_status' => 'Single',
            'nationality' => 'Filipino',
            'phone' => '09170000011',
            'email' => 'jane.patient@example.test',
            'verified' => true,
        ]);

        $response = $this->withSession(['_token' => 'test-token'])
            ->actingAs($user, 'web')
            ->post('/portal/pre-register', [
                '_token' => 'test-token',
                'first_name' => 'Jane',
                'middle_name' => 'M.',
                'last_name' => 'Patient',
                'suffix' => 'Jr.',
                'date_of_birth' => '1990-05-15',
                'sex' => 'Female',
                'civil_status' => 'Single',
                'nationality' => 'Filipino',
                'phone' => '09170000011',
                'email' => 'jane.patient@example.test',
                'address_line1' => '123 Sample Street',
                'address_barangay' => 'Bahay Toro',
                'address_city' => 'Quezon City',
                'address_province' => 'Metro Manila',
                'address_postal' => '1100',
                'allergies' => 'Penicillin',
                'emergency_name' => 'John Patient',
                'emergency_phone' => '09170000099',
                'emergency_relationship' => 'Spouse',
                'visit_reason' => 'Follow-up for recurring abdominal pain',
            ]);

        $response->assertRedirect(route('patients.portal'));

        $profile = $patient->preArrivalProfiles()->latest()->first();
        $this->assertNotNull($profile);
        $this->assertSame('Jane', $profile->first_name);
        $this->assertSame('Patient', $profile->last_name);
        $this->assertSame('1990-05-15', $profile->date_of_birth->format('Y-m-d'));
        $this->assertSame('Female', $profile->sex);
        $this->assertSame('123 Sample Street', $profile->address_line1);
        $this->assertSame('Bahay Toro', $profile->address_barangay);
        $this->assertSame('Quezon City', $profile->address_city);
        $this->assertSame('Metro Manila', $profile->address_province);
        $this->assertSame('1100', $profile->address_postal);
        $this->assertSame('John Patient', $profile->emergency_name);
        $this->assertSame('09170000099', $profile->emergency_phone);
        $this->assertSame('Spouse', $profile->emergency_relationship);
        $this->assertSame('Penicillin', $profile->allergies);
    }

    public function test_lookup_returns_pre_registration_fields_for_walk_in_registration(): void
    {
        $role = Role::where('name', 'registration')->firstOrFail();
        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'mrn' => 'MRN-LOOKUP-001',
            'first_name' => 'Lookup',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-05-15',
            'sex' => 'Female',
            'phone' => '09170000011',
            'email' => 'lookup.patient@example.test',
            'verified' => true,
        ]);

        $profile = $patient->preArrivalProfiles()->create([
            'token' => (string) Uuid::uuid4(),
            'reference_code' => 'PAC-9988',
            'status' => 'pending',
            'first_name' => 'Lookup',
            'middle_name' => 'Mae',
            'last_name' => 'Patient',
            'suffix' => 'Jr.',
            'date_of_birth' => '1990-05-15',
            'sex' => 'Female',
            'civil_status' => 'Single',
            'nationality' => 'Filipino',
            'phone' => '09170000011',
            'email' => 'lookup.patient@example.test',
            'allergies' => 'Penicillin',
            'emergency_name' => 'John Patient',
            'emergency_relationship' => 'Spouse',
            'emergency_phone' => '09170000099',
            'address_line1' => '123 Sample Street',
            'address_barangay' => 'Bahay Toro',
            'address_city' => 'Quezon City',
            'address_province' => 'Metro Manila',
            'address_postal' => '1100',
            'visit_reason' => 'Follow-up visit',
        ]);

        $response = $this->actingAs($user, 'web')->get('/patients/lookup?q=' . urlencode($profile->reference_code));

        $response->assertOk();
        $response->assertJsonPath('data.0.reference_code', 'PAC-9988');
        $response->assertJsonPath('data.0.middle_name', 'Mae');
        $response->assertJsonPath('data.0.suffix', 'Jr.');
        $response->assertJsonPath('data.0.civil_status', 'Single');
        $response->assertJsonPath('data.0.nationality', 'Filipino');
        $response->assertJsonPath('data.0.allergies', 'Penicillin');
        $response->assertJsonPath('data.0.address.line1', '123 Sample Street');
        $response->assertJsonPath('data.0.emergency_contact.relationship', 'Spouse');
        $response->assertJsonPath('data.0.emergency_contact.phone', '09170000099');
    }

    public function test_lookup_returns_the_matching_reference_code_when_patient_has_multiple_pre_arrival_profiles(): void
    {
        $role = Role::where('name', 'registration')->firstOrFail();
        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'mrn' => 'MRN-LOOKUP-MULTI-001',
            'first_name' => 'Reference',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-05-15',
            'sex' => 'Female',
            'phone' => '09170000088',
            'email' => 'reference.patient@example.test',
            'verified' => true,
        ]);

        $oldProfile = $patient->preArrivalProfiles()->create([
            'token' => (string) Uuid::uuid4(),
            'reference_code' => 'PAC-1001',
            'status' => 'pending',
            'first_name' => 'Older',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-05-15',
            'sex' => 'Female',
            'visit_reason' => 'Older visit',
        ]);

        $newProfile = $patient->preArrivalProfiles()->create([
            'token' => (string) Uuid::uuid4(),
            'reference_code' => 'PAC-2002',
            'status' => 'pending',
            'first_name' => 'Newest',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-05-15',
            'sex' => 'Female',
            'visit_reason' => 'Newest visit',
        ]);

        $response = $this->actingAs($user, 'web')->get('/patients/lookup?q=' . urlencode($newProfile->reference_code));

        $response->assertOk();
        $response->assertJsonPath('data.0.reference_code', 'PAC-2002');
        $response->assertJsonPath('data.0.first_name', 'Newest');
        $response->assertJsonPath('data.0.last_name', 'Patient');
    }

    public function test_reference_codes_are_generated_and_unique(): void
    {
        $firstPatient = Patient::create([
            'user_id' => null,
            'mrn' => 'MRN-REF-001',
            'first_name' => 'First',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-01-01',
            'sex' => 'Female',
            'phone' => '09170000001',
            'email' => 'first.patient@example.test',
            'verified' => true,
        ]);

        $secondPatient = Patient::create([
            'user_id' => null,
            'mrn' => 'MRN-REF-002',
            'first_name' => 'Second',
            'last_name' => 'Patient',
            'date_of_birth' => '1991-02-02',
            'sex' => 'Male',
            'phone' => '09170000002',
            'email' => 'second.patient@example.test',
            'verified' => true,
        ]);

        $first = PreArrivalProfile::create([
            'patient_id' => $firstPatient->id,
            'token' => (string) Uuid::uuid4(),
            'reference_code' => PreArrivalProfile::generateUniqueReferenceCode(),
            'status' => 'pending',
            'visit_reason' => 'Follow-up',
        ]);

        $second = PreArrivalProfile::create([
            'patient_id' => $secondPatient->id,
            'token' => (string) Uuid::uuid4(),
            'reference_code' => PreArrivalProfile::generateUniqueReferenceCode(),
            'status' => 'pending',
            'visit_reason' => 'Follow-up',
        ]);

        $this->assertNotSame($first->reference_code, $second->reference_code);
        $this->assertMatchesRegularExpression('/^PAC-\d{4}$/', $first->reference_code);
        $this->assertMatchesRegularExpression('/^PAC-\d{4}$/', $second->reference_code);
    }

    public function test_new_patient_can_pre_register_without_an_existing_account(): void
    {
        $response = $this->withSession(['_token' => 'test-token'])
            ->post('/pre-register', [
                '_token' => 'test-token',
                'first_name' => 'New',
                'last_name' => 'Patient',
                'date_of_birth' => '1995-06-20',
                'sex' => 'Female',
                'phone' => '09170000111',
                'email' => 'new.patient@example.test',
                'address_line1' => '10 New Street',
                'address_barangay' => 'Barangay One',
                'address_city' => 'Manila',
                'address_province' => 'Metro Manila',
                'address_postal' => '1000',
                'emergency_name' => 'New Contact',
                'emergency_relationship' => 'Parent',
                'emergency_phone' => '09170000112',
                'visit_reason' => 'New patient consultation',
                'medical_history' => 'None',
            ]);

        $response->assertOk();
        $response->assertSee('Reference code');

        $patient = Patient::where('email', 'new.patient@example.test')->firstOrFail();
        $this->assertFalse($patient->verified);
        $this->assertDatabaseHas('patient_addresses', [
            'patient_id' => $patient->id,
            'line1' => '10 New Street',
            'barangay' => 'Barangay One',
        ]);
        $this->assertDatabaseHas('emergency_contacts', [
            'patient_id' => $patient->id,
            'name' => 'New Contact',
            'relationship' => 'Parent',
        ]);
        $this->assertSame('pending', $patient->preArrivalProfiles()->firstOrFail()->status);
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
        $dashboard->assertDontSee('>Telehealth</a>', false);
        $dashboard->assertDontSee('Care Delivery');
        $dashboard->assertDontSee('Hospital Systems');
        $dashboard->assertSee('Pre-register for your visit');

        $portal = $this->actingAs($user, 'web')->get('/patient-portal');
        $portal->assertDontSee('Hospital Systems');
        $portal->assertSee('Pre-register for your visit');
    }
}
