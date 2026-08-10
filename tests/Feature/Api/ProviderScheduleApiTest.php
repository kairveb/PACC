<?php

namespace Tests\Feature\Api;

use App\Models\Provider;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\HimsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProviderScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HimsSeeder::class);
    }

    public function test_registration_can_view_provider_schedules(): void
    {
        $user = User::where('email', 'registration@coor.test')->first();
        $provider = Provider::first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/provider-schedules');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_registration_can_create_provider_schedule(): void
    {
        $user = User::where('email', 'registration@coor.test')->first();
        $provider = Provider::first();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/provider-schedules', [
            'provider_id' => $provider->id,
            'day_of_week' => 4,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'slot_duration' => 20,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.provider_id', $provider->id);
    }
}
