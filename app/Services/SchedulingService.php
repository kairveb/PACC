<?php

namespace App\Services;

use App\Models\AppointmentSlot;
use App\Models\ProviderSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SchedulingService
{
    /**
     * Generate appointment slots for a provider schedule for a given date range.
     */
    public function generateSlots(ProviderSchedule $schedule, string $fromDate, string $toDate): int
    {
        $count = 0;
        $start = Carbon::parse($fromDate);
        $end = Carbon::parse($toDate);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if (!$this->shouldGenerateForDate($schedule, $date)) {
                continue;
            }

            $slotStart = $date->copy()->setTimeFromTimeString($schedule->start_time);
            $slotEnd = $date->copy()->setTimeFromTimeString($schedule->end_time);
            $duration = $schedule->slot_duration ?: 30;

            while ($slotStart->addMinutes($duration)->lte($slotEnd)) {
                $blockStart = $slotStart->copy()->subMinutes($duration);
                $blockEnd = $slotStart->copy();

                if ($this->isInBreakWindow($schedule, $date, $blockStart, $blockEnd)) {
                    continue;
                }

                AppointmentSlot::firstOrCreate(
                    ['provider_id' => $schedule->provider_id, 'starts_at' => $blockStart],
                    ['ends_at' => $blockEnd, 'status' => AppointmentSlot::STATUS_AVAILABLE]
                );
                $count++;
            }
        }

        return $count;
    }

    protected function shouldGenerateForDate(ProviderSchedule $schedule, Carbon $date): bool
    {
        if ($date->dayOfWeek !== $schedule->day_of_week) {
            return false;
        }

        return !($schedule->unavailable_date && $schedule->unavailable_date->isSameDay($date));
    }

    protected function isInBreakWindow(ProviderSchedule $schedule, Carbon $date, Carbon $blockStart, Carbon $blockEnd): bool
    {
        if (!$schedule->break_start || !$schedule->break_end) {
            return false;
        }

        $breakStart = $date->copy()->setTimeFromTimeString($schedule->break_start);
        $breakEnd = $date->copy()->setTimeFromTimeString($schedule->break_end);

        return $blockStart->lt($breakEnd) && $blockEnd->gt($breakStart);
    }

    /**
     * Get available slots for a provider on a given date (or date range).
     */
    public function availableSlots(int $providerId, string $date, ?int $appointmentTypeId = null)
    {
        return $this->buildAvailableSlotsQuery($providerId, $date, $appointmentTypeId)->get();
    }

    protected function buildAvailableSlotsQuery(int $providerId, string $date, ?int $appointmentTypeId = null)
    {
        return AppointmentSlot::where('provider_id', $providerId)
            ->whereDate('starts_at', $date)
            ->where('status', AppointmentSlot::STATUS_AVAILABLE)
            ->when($appointmentTypeId, fn ($q) => $q->where('appointment_type_id', $appointmentTypeId))
            ->orderBy('starts_at');
    }

    /**
     * Check if a provider has a conflicting appointment in the given window.
     */
    public function providerHasConflict(int $providerId, Carbon $startsAt, Carbon $endsAt, ?int $ignoreAppointmentId = null): bool
    {
        $query = \App\Models\Appointment::where('provider_id', $providerId)
            ->whereIn('status', ['PENDING', 'CONFIRMED', 'CHECKED-IN', 'IN-CONSULTATION'])
            ->where(function ($q) use ($startsAt, $endsAt) {
                $q->whereBetween('starts_at', [$startsAt, $endsAt->copy()->subSecond()])
                    ->orWhereBetween('ends_at', [$startsAt->copy()->addSecond(), $endsAt])
                    ->orWhere(function ($q) use ($startsAt, $endsAt) {
                        $q->where('starts_at', '<=', $startsAt)
                            ->where('ends_at', '>=', $endsAt);
                    });
            });

        if ($ignoreAppointmentId) {
            $query->where('id', '!=', $ignoreAppointmentId);
        }

        return $query->exists();
    }

    /**
     * Atomically reserve a slot. Returns true if successfully claimed.
     */
    public function claimSlot(int $slotId): bool
    {
        return DB::transaction(function () use ($slotId) {
            return $this->claimAvailableSlot($slotId);
        });
    }

    protected function claimAvailableSlot(int $slotId): bool
    {
        $updated = AppointmentSlot::where('id', $slotId)
            ->where('status', AppointmentSlot::STATUS_AVAILABLE)
            ->lockForUpdate()
            ->update(['status' => AppointmentSlot::STATUS_BOOKED]);

        return $updated > 0;
    }
}
