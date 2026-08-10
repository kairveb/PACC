<?php

namespace Tests\Unit;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Patient;
use App\Models\Room;
use App\Models\User;
use App\Models\Ward;
use App\Services\AuditLogService;
use App\Services\BedManagementService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class BedManagementServiceAssignTest extends TestCase
{
    public function test_assign_bed_creates_an_active_assignment_for_an_admission(): void
    {
        $audit = Mockery::mock(AuditLogService::class);
        $audit->shouldReceive('assignBed')->once()->andReturnUsing(fn ($admissionId, $bedId) => new AuditLog([
            'user_id' => null,
            'action' => 'ASSIGN_BED',
            'resource_type' => 'bed_assignment',
            'resource_id' => $bedId,
            'result' => 'SUCCESS',
            'metadata' => ['admission_id' => $admissionId],
        ]));

        $service = new BedManagementService($audit);

        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $user = User::factory()->create();
        $patient = Patient::create([
            'mrn' => 'MRN-2026-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'date_of_birth' => '1992-05-05',
            'sex' => 'Female',
            'verified' => false,
            'user_id' => $user->id,
        ]);
        $admission = Admission::create([
            'admission_number' => 'ADM-2026-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'patient_id' => $patient->id,
            'status' => Admission::STATUS_REQUESTED,
            'created_by' => $user->id,
        ]);
        $ward = Ward::create([
            'code' => 'WARD-' . random_int(1000, 9999),
            'name' => 'Ward ' . random_int(1000, 9999),
            'type' => 'GENERAL',
            'active' => true,
        ]);
        $room = Room::create([
            'ward_id' => $ward->id,
            'number' => 'ROOM-' . random_int(1000, 9999),
            'type' => 'STANDARD',
        ]);
        $bed = Bed::create([
            'room_id' => $room->id,
            'number' => 'BED-' . random_int(1000, 9999),
            'status' => Bed::STATUS_AVAILABLE,
        ]);

        $assignment = $service->assignBed($admission, $bed->id);

        $this->assertInstanceOf(BedAssignment::class, $assignment);
        $this->assertSame($admission->id, $assignment->admission_id);
        $this->assertSame($bed->id, $assignment->bed_id);
        $this->assertSame(BedAssignment::STATUS_ACTIVE, $assignment->status);
    }
}
