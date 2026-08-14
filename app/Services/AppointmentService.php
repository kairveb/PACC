<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\AppointmentSlot;
use App\Models\Waitlist;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    public function __construct(
        protected SchedulingService $scheduling,
        protected AuditLogService $audit,
        protected TelehealthService $telehealth
    ) {
    }

    /**
     * Generate the next appointment number.
     */
    public function generateNumber(): string
    {
        $year = now()->format('Y');
        $last = Appointment::where('appointment_number', 'like', "APT-{$year}-%")
            ->orderByDesc('appointment_number')
            ->lockForUpdate()
            ->first();

        $sequence = 1;
        if ($last) {
            $parts = explode('-', $last->appointment_number);
            $seq = str_pad((string) ((int) $parts[2]) + 1, 6, '0', STR_PAD_LEFT);
            return "APT-{$year}-{$seq}";
        }

        return "APT-{$year}-000001";
    }

    /**
     * Book an appointment with double-booking prevention.
     */
    public function book(array $data, ?int $userId = null): Appointment
    {
        return DB::transaction(function () use ($data, $userId) {
            $startsAt = Carbon::parse($data['starts_at']);
            $duration = $data['duration'] ?? 30;
            $endsAt = $startsAt->copy()->addMinutes($duration);

            $this->ensureNoConflict($data['provider_id'], $startsAt, $endsAt);
            $this->claimOptionalSlot($data['appointment_slot_id'] ?? null);

            $appointment = $this->createAppointmentRecord($data, $startsAt, $endsAt, $userId);
            $this->recordInitialStatusHistory($appointment, $userId);
            $this->provisionTelehealthIfNeeded($appointment);
            $this->audit->createAppointment($appointment->id, ['appointment_number' => $appointment->appointment_number]);

            return $appointment;
        });
    }

    protected function ensureNoConflict(int $providerId, Carbon $startsAt, Carbon $endsAt): void
    {
        if ($this->scheduling->providerHasConflict($providerId, $startsAt, $endsAt)) {
            throw ValidationException::withMessages([
                'starts_at' => 'This provider already has an appointment that conflicts with the selected time.',
            ]);
        }
    }

    protected function claimOptionalSlot(?int $slotId): void
    {
        if (!$slotId) {
            return;
        }

        $claimed = $this->scheduling->claimSlot($slotId);
        if (!$claimed) {
            throw ValidationException::withMessages([
                'starts_at' => 'This time slot is no longer available.',
            ]);
        }
    }

    protected function createAppointmentRecord(array $data, Carbon $startsAt, Carbon $endsAt, ?int $userId): Appointment
    {
        return Appointment::create([
            'appointment_number' => $this->generateNumber(),
            'patient_id' => $data['patient_id'],
            'provider_id' => $data['provider_id'],
            'department_id' => $data['department_id'] ?? null,
            'appointment_type_id' => $data['appointment_type_id'] ?? null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $data['status'] ?? Appointment::STATUS_PENDING,
            'reason' => $data['reason'] ?? null,
            'created_by' => $userId,
        ]);
    }

    protected function recordInitialStatusHistory(Appointment $appointment, ?int $userId): void
    {
        AppointmentStatusHistory::create([
            'appointment_id' => $appointment->id,
            'from_status' => null,
            'to_status' => $appointment->status,
            'changed_by' => $userId,
            'reason' => 'Appointment booked',
        ]);
    }

    protected function provisionTelehealthIfNeeded(Appointment $appointment): void
    {
        if ($appointment->appointmentType?->telehealth) {
            $this->telehealth->createSession($appointment);
        }
    }

    /**
     * Transition an appointment to a new status with validation.
     */
    public function transitionStatus(Appointment $appointment, string $toStatus, ?int $userId = null, ?string $reason = null): Appointment
    {
        $allowed = $this->allowedTransitions($appointment->status);

        if (!in_array($toStatus, $allowed)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition appointment from {$appointment->status} to {$toStatus}.",
            ]);
        }

        return DB::transaction(function () use ($appointment, $toStatus, $userId, $reason) {
            $from = $appointment->status;
            $appointment->update(['status' => $toStatus]);

            if ($toStatus === Appointment::STATUS_CONFIRMED && $appointment->appointmentType?->telehealth) {
                $this->telehealth->createSession($appointment);
            }

            if ($toStatus === Appointment::STATUS_CANCELLED && $appointment->telehealthSession) {
                $this->telehealth->cancel($appointment->telehealthSession);
            }

            AppointmentStatusHistory::create([
                'appointment_id' => $appointment->id,
                'from_status' => $from,
                'to_status' => $toStatus,
                'changed_by' => $userId,
                'reason' => $reason,
            ]);

            return $appointment;
        });
    }

    public function checkIn(Appointment $appointment, ?int $userId = null): Appointment
    {
        return $this->transitionStatus($appointment, Appointment::STATUS_CHECKED_IN, $userId, 'Patient checked in');
    }

    public function cancel(Appointment $appointment, ?int $userId = null, ?string $reason = null): Appointment
    {
        $this->releaseSlot($appointment);
        $this->audit->cancelAppointment($appointment->id, ['reason' => $reason]);
        return $this->transitionStatus($appointment, Appointment::STATUS_CANCELLED, $userId, $reason ?? 'Cancelled');
    }

    public function markNoShow(Appointment $appointment, ?int $userId = null): Appointment
    {
        return $this->transitionStatus($appointment, Appointment::STATUS_NO_SHOW, $userId, 'Patient did not show');
    }

    /**
     * Reschedule an appointment to a new time, preventing conflicts.
     */
    public function reschedule(Appointment $appointment, array $data, ?int $userId = null): Appointment
    {
        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = $startsAt->copy()->addMinutes($data['duration'] ?? 30);

        if ($this->scheduling->providerHasConflict($appointment->provider_id, $startsAt, $endsAt, $appointment->id)) {
            throw ValidationException::withMessages([
                'starts_at' => 'This provider already has an appointment that conflicts with the selected time.',
            ]);
        }

        return DB::transaction(function () use ($appointment, $startsAt, $endsAt, $userId) {
            $this->releaseSlot($appointment);
            $appointment->update([
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => Appointment::STATUS_CONFIRMED,
            ]);

            AppointmentStatusHistory::create([
                'appointment_id' => $appointment->id,
                'from_status' => 'RESCHEDULED',
                'to_status' => Appointment::STATUS_CONFIRMED,
                'changed_by' => $userId,
                'reason' => 'Rescheduled',
            ]);

            $this->audit->rescheduleAppointment($appointment->id);

            return $appointment;
        });
    }

    /**
     * Release the appointment slot if one was claimed.
     */
    protected function releaseSlot(Appointment $appointment): void
    {
        AppointmentSlot::where('provider_id', $appointment->provider_id)
            ->where('starts_at', $appointment->starts_at)
            ->where('status', AppointmentSlot::STATUS_BOOKED)
            ->update(['status' => AppointmentSlot::STATUS_AVAILABLE]);
    }

    /**
     * Add a patient to the waitlist.
     */
    public function addToWaitlist(array $data): Waitlist
    {
        return Waitlist::create($data);
    }

    protected function allowedTransitions(string $from): array
    {
        return match ($from) {
            Appointment::STATUS_PENDING => [Appointment::STATUS_CONFIRMED, Appointment::STATUS_CANCELLED],
            Appointment::STATUS_CONFIRMED => [Appointment::STATUS_CHECKED_IN, Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW],
            Appointment::STATUS_CHECKED_IN => [Appointment::STATUS_IN_CONSULTATION, Appointment::STATUS_CANCELLED],
            Appointment::STATUS_IN_CONSULTATION => [Appointment::STATUS_COMPLETED],
            Appointment::STATUS_COMPLETED => [],
            Appointment::STATUS_CANCELLED => [],
            Appointment::STATUS_NO_SHOW => [],
            default => [],
        };
    }
}
