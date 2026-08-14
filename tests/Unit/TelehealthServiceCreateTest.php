<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Provider;
use App\Models\TelehealthSession;
use App\Models\User;
use App\Services\TelehealthService;
use App\Services\ZoomService;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;

class TelehealthServiceCreateTest extends TestCase
{
    public function test_create_session_creates_a_session_with_default_status(): void
    {
        $zoom = Mockery::mock(ZoomService::class);
        $zoom->shouldReceive('enabled')->once()->andReturn(false);

        $service = new TelehealthService($zoom);

        $user = User::factory()->create();
        $patient = Patient::create([
            'mrn' => 'MRN-2026-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'first_name' => 'Mina',
            'last_name' => 'Sato',
            'date_of_birth' => '1985-01-01',
            'sex' => 'Female',
            'verified' => false,
            'user_id' => $user->id,
        ]);
        $provider = Provider::create([
            'user_id' => $user->id,
            'department_id' => null,
            'display_name' => 'Dr. Sato',
            'active' => true,
        ]);
        $appointment = Appointment::create([
            'appointment_number' => 'APT-2026-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'starts_at' => Carbon::now(),
            'ends_at' => Carbon::now()->addMinutes(30),
            'status' => Appointment::STATUS_PENDING,
        ]);

        $session = $service->createSession($appointment);

        $this->assertInstanceOf(TelehealthSession::class, $session);
        $this->assertSame($appointment->id, $session->appointment_id);
        $this->assertSame(TelehealthSession::STATUS_SCHEDULED, $session->status);
        $this->assertNotNull($session->join_url);
        $this->assertStringContainsString('/telehealth/' . $session->id . '/join', $session->join_url);
    }
}
