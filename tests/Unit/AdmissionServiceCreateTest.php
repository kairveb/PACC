<?php

namespace Tests\Unit;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\User;
use App\Services\AdmissionService;
use App\Services\AuditLogService;
use App\Services\BedManagementService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class AdmissionServiceCreateTest extends TestCase
{
    public function test_create_persists_an_admission_and_audit_log(): void
    {
        $bedManagement = Mockery::mock(BedManagementService::class);
        $audit = Mockery::mock(AuditLogService::class);
        $audit->shouldReceive('createAdmission')->once()->andReturnUsing(fn ($id) => new AuditLog([
            'user_id' => null,
            'action' => 'CREATE_ADMISSION',
            'resource_type' => 'admission',
            'resource_id' => $id,
            'result' => 'SUCCESS',
        ]));

        $service = new AdmissionService($bedManagement, $audit);

        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $user = User::factory()->create();
        $patient = Patient::create([
            'mrn' => 'MRN-2026-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'first_name' => 'Margaret',
            'last_name' => 'Hamilton',
            'date_of_birth' => '1936-08-17',
            'sex' => 'Female',
            'verified' => false,
            'user_id' => $user->id,
        ]);

        $admission = $service->create([
            'patient_id' => $patient->id,
            'reason' => 'Observation',
        ]);

        $this->assertInstanceOf(Admission::class, $admission);
        $this->assertSame($patient->id, $admission->patient_id);
        $this->assertSame(Admission::STATUS_REQUESTED, $admission->status);
    }
}
