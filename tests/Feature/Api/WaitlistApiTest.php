<?php

namespace Tests\Feature\Api;

use App\Models\AppointmentType;
use App\Models\Patient;
use App\Models\Provider;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\HimsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WaitlistApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HimsSeeder::class);
    }

    public function test_registration_can_add_patient_to_waitlist(): void
    {
        $user = User::where('email', 'registration@coor.test')->first();
        $patient = Patient::first();
        $provider = Provider::first();
        $appointmentType = AppointmentType::first();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/waitlists', [
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'appointment_type_id' => $appointmentType->id,
            'preferred_date' => now()->addDays(3)->toDateString(),
            'status' => 'WAITING',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'WAITING');

        $this->assertDatabaseHas('waitlists', ['patient_id' => $patient->id, 'provider_id' => $provider->id]);
    }
}
