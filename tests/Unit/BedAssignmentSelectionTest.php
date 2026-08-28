<?php

namespace Tests\Unit;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Patient;
use App\Models\Room;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BedAssignmentSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_bed_assignment_prefers_the_latest_assignment_for_an_admission(): void
    {
        $user = User::factory()->create();

        $patient = Patient::create([
            'mrn' => 'MRN-2026-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'first_name' => 'Taylor',
            'last_name' => 'Ng',
            'date_of_birth' => '1990-01-01',
            'sex' => 'Female',
            'verified' => false,
            'user_id' => $user->id,
        ]);

        $admission = Admission::create([
            'admission_number' => 'ADM-2026-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'patient_id' => $patient->id,
            'status' => Admission::STATUS_ADMITTED,
            'created_by' => $user->id,
            'admitted_at' => now()->subDays(2),
        ]);

        $ward = Ward::create([
            'code' => 'WARD-' . random_int(1000, 9999),
            'name' => 'Ward ' . random_int(1000, 9999),
            'type' => 'GENERAL',
            'active' => true,
        ]);

        $roomA = Room::create([
            'ward_id' => $ward->id,
            'number' => 'ROOM-' . random_int(1000, 9999),
            'type' => 'STANDARD',
        ]);

        $roomB = Room::create([
            'ward_id' => $ward->id,
            'number' => 'ROOM-' . random_int(1000, 9999),
            'type' => 'STANDARD',
        ]);

        $oldBed = Bed::create([
            'room_id' => $roomA->id,
            'number' => 'BED-' . random_int(1000, 9999),
            'status' => Bed::STATUS_OCCUPIED,
        ]);

        $newBed = Bed::create([
            'room_id' => $roomB->id,
            'number' => 'BED-' . random_int(1000, 9999),
            'status' => Bed::STATUS_OCCUPIED,
        ]);

        BedAssignment::create([
            'admission_id' => $admission->id,
            'bed_id' => $oldBed->id,
            'assigned_by' => $user->id,
            'assigned_at' => now()->subDays(2),
            'status' => BedAssignment::STATUS_ACTIVE,
        ]);

        BedAssignment::create([
            'admission_id' => $admission->id,
            'bed_id' => $newBed->id,
            'assigned_by' => $user->id,
            'assigned_at' => now()->subMinutes(30),
            'status' => BedAssignment::STATUS_ACTIVE,
        ]);

        $this->assertNotNull($admission->fresh()->activeBedAssignment);
        $this->assertSame($newBed->id, $admission->fresh()->activeBedAssignment->bed_id);
    }
}
