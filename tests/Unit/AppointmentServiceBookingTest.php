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

    public function test_provider_full_name_uses_display_name_for_booking_labels(): void
    {
        $user = User::factory()->create();

        $provider = Provider::create([
            'user_id' => $user->id,
            'department_id' => null,
            'display_name' => 'Dr. Elena Santos',
            'active' => true,
        ]);

        $this->assertSame('Dr. Elena Santos', $provider->full_name);
    }

    public function test_reschedule_casts_string_duration_before_calculating_end_time(): void
    {
        $scheduling = Mockery::mock(SchedulingService::class);
        $audit = Mockery::mock(AuditLogService::class);
        $telehealth = Mockery::mock(TelehealthService::class);

        $scheduling->shouldReceive('providerHasConflict')->once()->andReturn(false);
        $audit->shouldReceive('rescheduleAppointment')->once()->withArgs(fn ($id) => (int) $id > 0);
        $telehealth->shouldReceive('createSession')->never();

        $service = new AppointmentService($scheduling, $audit, $telehealth);

        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $user = User::factory()->create();
        $patient = Patient::create([
            'mrn' => 'MRN-2026-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'date_of_birth' => '1991-02-02',
            'sex' => 'Female',
            'verified' => false,
            'user_id' => $user->id,
        ]);
        $provider = Provider::create([
            'user_id' => $user->id,
            'department_id' => null,
            'display_name' => 'Dr. Grace',
            'active' => true,
        ]);

        $appointment = Appointment::create([
            'appointment_number' => 'APT-2026-000777',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'starts_at' => Carbon::now()->addDays(1)->setTime(9, 0),
            'ends_at' => Carbon::now()->addDays(1)->setTime(9, 30),
            'status' => Appointment::STATUS_CONFIRMED,
            'reason' => 'Follow-up',
            'created_by' => $user->id,
        ]);

        $updated = $service->reschedule($appointment, [
            'starts_at' => Carbon::now()->addDays(2)->setTime(10, 0)->toDateTimeString(),
            'duration' => '30',
        ], $user->id);

        $this->assertSame(Appointment::STATUS_CONFIRMED, $updated->status);
        $this->assertSame('2026', $updated->starts_at->format('Y'));
    }
}
