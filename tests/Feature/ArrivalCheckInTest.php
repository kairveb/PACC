<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PreArrivalProfile;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\HimsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ArrivalCheckInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(HimsSeeder::class);
    }

    public function test_staff_can_look_up_a_valid_pre_arrival_token_and_see_prefilled_data(): void
    {
        $role = Role::where('name', 'nurse')->firstOrFail();
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'mrn' => 'MRN-CHECKIN-001',
            'first_name' => 'Jane',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-05-15',
            'sex' => 'Female',
            'phone' => '09170000011',
            'email' => 'jane.patient@example.test',
            'verified' => true,
        ]);

        $profile = $patient->preArrivalProfiles()->create([
            'token' => (string) Str::uuid(),
            'reference_code' => 'PAC-1234',
            'status' => 'pending',
            'visit_reason' => 'Follow-up for recurring abdominal pain',
            'medical_history' => 'Asthma',
            'current_medications' => 'Albuterol',
            'allergies' => 'Penicillin',
            'emergency_name' => 'John Patient',
            'emergency_phone' => '09170000099',
            'address_line1' => '123 Sample Street',
            'address_city' => 'Quezon City',
            'address_province' => 'Metro Manila',
            'address_postal' => '1100',
            'phone' => '09170000011',
            'email' => 'jane.patient@example.test',
            'qr_code_url' => 'https://example.test/qr.png',
        ]);

        $response = $this->actingAs($user, 'web')->get('/emergency/check-in/reference?reference_code=' . urlencode($profile->reference_code));

        $response->assertOk();
        $response->assertSee('Jane Patient');
        $response->assertSee('Follow-up for recurring abdominal pain');
        $response->assertSee('Penicillin');
    }

    public function test_staff_cannot_check_in_using_an_invalid_or_expired_token(): void
    {
        $role = Role::where('name', 'nurse')->firstOrFail();
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $response = $this->actingAs($user, 'web')->get('/emergency/check-in/not-a-valid-token');

        $response->assertRedirect(route('emergency.index'));
        $response->assertSessionHas('error');
    }

    public function test_patient_role_user_cannot_access_the_check_in_route(): void
    {
        $role = Role::where('name', 'patient')->firstOrFail();
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->roles()->syncWithoutDetaching([$role->id]);

        Patient::create([
            'user_id' => $user->id,
            'mrn' => 'MRN-CHECKIN-002',
            'first_name' => 'Access',
            'last_name' => 'Patient',
            'date_of_birth' => '1991-02-10',
            'sex' => 'Female',
            'phone' => '09170000088',
            'email' => 'access.patient@example.test',
            'verified' => true,
        ]);

        $this->actingAs($user, 'web')->get('/emergency/check-in/' . (string) Str::uuid())
            ->assertForbidden();
    }

    public function test_successful_check_in_updates_the_profile_status_and_timestamp(): void
    {
        $role = Role::where('name', 'doctor')->firstOrFail();
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'mrn' => 'MRN-CHECKIN-003',
            'first_name' => 'Arrival',
            'last_name' => 'Patient',
            'date_of_birth' => '1988-03-15',
            'sex' => 'Male',
            'phone' => '09170000089',
            'email' => 'arrival.patient@example.test',
            'verified' => true,
        ]);

        $profile = $patient->preArrivalProfiles()->create([
            'token' => (string) Str::uuid(),
            'status' => 'pending',
            'first_name' => 'Arrival',
            'last_name' => 'Patient',
            'date_of_birth' => '1988-03-15',
            'sex' => 'Male',
            'phone' => '09170000089',
            'email' => 'arrival.patient@example.test',
            'visit_reason' => 'Chest pain',
            'medical_history' => 'Hypertension',
            'allergies' => 'None',
            'address_line1' => '45 Arrival Street',
            'address_barangay' => 'Barangay Central',
            'address_city' => 'Manila',
            'address_province' => 'Metro Manila',
            'address_postal' => '1000',
            'emergency_name' => 'Alex Patient',
            'emergency_relationship' => 'Sibling',
            'emergency_phone' => '09170000090',
            'phone' => '09170000089',
            'email' => 'arrival.patient@example.test',
            'qr_code_url' => 'https://example.test/qr.png',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])
            ->actingAs($user, 'web')
            ->post('/emergency/check-in/confirm', ['_token' => 'test-token', 'token' => $profile->token]);

        $response->assertRedirect(route('patients.show', $patient));

        $profile->refresh();
        $this->assertSame('arrived', $profile->status);
        $this->assertNotNull($profile->arrived_at);

        $patient->refresh();
        $this->assertSame('Arrival', $patient->first_name);
        $this->assertSame('Patient', $patient->last_name);
        $this->assertSame('1988-03-15', $patient->date_of_birth->format('Y-m-d'));
        $this->assertSame('Male', $patient->sex);
        $this->assertSame('09170000089', $patient->phone);
        $this->assertSame('arrival.patient@example.test', $patient->email);
        $this->assertSame('None', $patient->allergies);

        $this->assertDatabaseHas('patient_addresses', [
            'patient_id' => $patient->id,
            'line1' => '45 Arrival Street',
            'barangay' => 'Barangay Central',
            'city' => 'Manila',
            'province' => 'Metro Manila',
            'postal_code' => '1000',
            'primary' => 1,
        ]);
        $this->assertDatabaseHas('emergency_contacts', [
            'patient_id' => $patient->id,
            'name' => 'Alex Patient',
            'relationship' => 'Sibling',
            'phone' => '09170000090',
        ]);
    }
}
