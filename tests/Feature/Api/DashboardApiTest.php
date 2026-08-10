<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\HimsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_dashboard_payload(): void
    {
        $this->seed(HimsSeeder::class);

        $user = User::where('email', 'doctor@coor.test')->firstOrFail();
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/dashboard');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email', 'role', 'roles'],
                'overview' => ['today_patients', 'today_appointments', 'er_patients', 'available_beds', 'occupied_beds', 'telehealth_appointments'],
                'items' => ['appointments', 'encounters', 'patient_count'],
            ],
        ]);

        $this->assertSame('doctor', $response->json('data.user.role'));
    }

    public function test_nurse_dashboard_payload_contains_role_specific_queue_data(): void
    {
        $this->seed(HimsSeeder::class);

        $user = User::where('email', 'nurse@coor.test')->firstOrFail();
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/dashboard');

        $response->assertOk();
        $response->assertJsonPath('data.user.role', 'nurse');
        $queue = $response->json('data.items.queue');
        $this->assertNotEmpty($queue);
        $this->assertIsString($queue[0]['title']);
    }

    public function test_doctor_dashboard_payload_includes_role_summary_counts(): void
    {
        $this->seed(HimsSeeder::class);

        $user = User::where('email', 'doctor@coor.test')->firstOrFail();
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/dashboard');

        $response->assertOk();
        $response->assertJsonPath('data.user.role', 'doctor');
        $response->assertJsonPath('data.summary.appointments_today', $response->json('data.overview.today_appointments'));
        $response->assertJsonPath('data.summary.patients_today', $response->json('data.overview.today_patients'));
    }
}
