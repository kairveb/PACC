<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\Room;
use App\Models\Ward;
use Database\Seeders\HimsSeeder;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BedBoardRoomNumberTest extends TestCase
{
    public function test_room_numbers_are_unique_across_the_bed_board(): void
    {
        Artisan::call('db:seed', ['--class' => HimsSeeder::class]);

        $duplicateRoomNumbers = Room::query()
            ->select('number')
            ->groupBy('number')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('number');

        $this->assertTrue(
            $duplicateRoomNumbers->isEmpty(),
            'Room numbers should be unique across the bed board to avoid repeated labels like Room 201.'
        );
    }

    public function test_demo_seed_uses_med_201_bed_a_as_occupied_and_bed_b_as_available(): void
    {
        Artisan::call('db:seed', ['--class' => HimsSeeder::class]);

        $ward = Ward::query()->where('code', 'MED')->firstOrFail();
        $room = $ward->rooms()->where('number', '201')->firstOrFail();

        $occupiedBed = Bed::query()->where('room_id', $room->id)->where('number', 'A')->firstOrFail();
        $availableBed = Bed::query()->where('room_id', $room->id)->where('number', 'B')->firstOrFail();

        $this->assertSame('OCCUPIED', $occupiedBed->status);
        $this->assertSame('AVAILABLE', $availableBed->status);
        $this->assertSame('MED / 201 / Bed A', $occupiedBed->label);
        $this->assertSame('MED / 201 / Bed B', $availableBed->label);
    }
}
