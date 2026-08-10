<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Provider;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\AuditLogService;
use App\Services\SchedulingService;
use App\Services\TelehealthService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class AppointmentServiceBookingTest extends TestCase
{
    public function test_book_creates_an_appointment_record_when_no_conflict_exists(): void
    {
        $scheduling = Mockery::mock(SchedulingService::class);
        $audit = Mockery::mock(AuditLogService::class);
        $telehealth = Mockery::mock(TelehealthService::class);

        $scheduling->shouldReceive('providerHasConflict')->once()->andReturn(false);
        $scheduling->shouldReceive('claimSlot')->never();
        $audit->shouldReceive('createAppointment')->once()->andReturnUsing(fn ($id, $meta) => new AuditLog([
            'user_id' => null,
            'action' => 'CREATE_APPOINTMENT',
            'resource_type' => 'appointment',
            'resource_id' => $id,
            'result' => 'SUCCESS',
            'metadata' => $meta,
        ]));
        $telehealth->shouldReceive('createSession')->never();

        $service = new AppointmentService($scheduling, $audit, $telehealth);

        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $user = User::factory()->create();
        $patient = Patient::create([
            'mrn' => 'MRN-2026-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'date_of_birth' => '1990-01-01',
            'sex' => 'Female',
            'verified' => false,
            'user_id' => $user->id,
        ]);
        $provider = Provider::create([
            'user_id' => $user->id,
            'department_id' => null,
            'display_name' => 'Dr. Ada',
            'active' => true,
        ]);

        $appointment = $service->book([
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'starts_at' => Carbon::now()->addHour()->toDateTimeString(),
            'duration' => 30,
            'reason' => 'Follow-up',
        ], $user->id);

        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertSame('PENDING', $appointment->status);
        $this->assertSame($patient->id, $appointment->patient_id);
        $this->assertSame($provider->id, $appointment->provider_id);
    }
}
