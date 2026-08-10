<?php

namespace Tests\Unit;

use App\Models\AppointmentSlot;
use App\Models\Provider;
use App\Models\ProviderSchedule;
use App\Models\User;
use App\Services\SchedulingService;
use Carbon\Carbon;
use Tests\TestCase;

class SchedulingServiceGenerateSlotsTest extends TestCase
{
    public function test_generate_slots_creates_available_slots_for_the_schedule(): void
    {
        $service = new SchedulingService();

        $user = User::factory()->create();
        $provider = Provider::create([
            'user_id' => $user->id,
            'department_id' => null,
            'display_name' => 'Dr. Scheduler',
            'active' => true,
        ]);

        $schedule = new ProviderSchedule();
        $schedule->provider_id = $provider->id;
        $schedule->day_of_week = Carbon::parse('2026-08-10')->dayOfWeek;
        $schedule->start_time = '09:00:00';
        $schedule->end_time = '10:00:00';
        $schedule->slot_duration = 30;
        $schedule->break_start = null;
        $schedule->break_end = null;
        $schedule->unavailable_date = null;

        $count = $service->generateSlots($schedule, '2026-08-10', '2026-08-10');

        $this->assertSame(2, $count);
        $this->assertSame(2, AppointmentSlot::where('provider_id', $provider->id)->count());
    }
}
