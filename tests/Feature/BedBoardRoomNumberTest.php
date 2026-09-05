<?php

namespace Tests\Feature;

use App\Models\Room;
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
}
