<?php

namespace Tests\Feature\Api;

use App\Models\Appointment;
use App\Models\Provider;
use App\Models\Role;
use App\Models\TelehealthSession;
use App\Models\User;
use Database\Seeders\HimsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TelehealthParticipantApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HimsSeeder::class);
    }

    public function test_doctor_can_add_participant_to_telehealth_session(): void
    {
        $user = User::where('email', 'doctor@coor.test')->first();
        $appointment = Appointment::first();
        Sanctum::actingAs($user);

        $session = TelehealthSession::create([
            'appointment_id' => $appointment->id,
            'start_time' => now()->addDay(),
            'duration' => 30,
            'status' => 'SCHEDULED',
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/telehealth/{$session->id}/participants", [
            'participant_type' => 'provider',
            'participant_id' => $user->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.role', 'provider');
    }
}
