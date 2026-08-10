<?php

namespace Tests\Feature\Api;

use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Provider;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\HimsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EncounterNoteApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HimsSeeder::class);
    }

    public function test_doctor_can_create_encounter_note(): void
    {
        $user = User::where('email', 'doctor@coor.test')->first();
        $patient = Patient::first();
        $provider = Provider::first();
        $encounter = Encounter::create([
            'encounter_number' => 'ENC-2026-000999',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'type' => 'consultation',
            'started_at' => now(),
            'status' => 'OPEN',
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/encounters/{$encounter->id}/notes", [
            'body' => 'Patient shows improved symptoms.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.body', 'Patient shows improved symptoms.');

        $this->assertDatabaseHas('encounter_notes', ['encounter_id' => $encounter->id, 'body' => 'Patient shows improved symptoms.']);
    }
}
