<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\ErVisit;
use App\Models\Patient;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\TriageService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class TriageServiceRegisterTest extends TestCase
{
    public function test_register_er_visit_persists_a_visit_and_audit_log(): void
    {
        $audit = Mockery::mock(AuditLogService::class);
        $audit->shouldReceive('createErVisit')->once()->andReturnUsing(fn ($id) => new AuditLog([
            'user_id' => null,
            'action' => 'CREATE_ER_VISIT',
            'resource_type' => 'er_visit',
            'resource_id' => $id,
            'result' => 'SUCCESS',
        ]));

        $service = new TriageService($audit);

        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $user = User::factory()->create();
        $patient = Patient::create([
            'mrn' => 'MRN-2026-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'first_name' => 'Katherine',
            'last_name' => 'Johnson',
            'date_of_birth' => '1918-08-26',
            'sex' => 'Female',
            'verified' => false,
            'user_id' => $user->id,
        ]);

        $visit = $service->registerErVisit([
            'patient_id' => $patient->id,
            'chief_complaint' => 'Chest pain',
        ]);

        $this->assertInstanceOf(ErVisit::class, $visit);
        $this->assertSame($patient->id, $visit->patient_id);
        $this->assertSame(ErVisit::STATUS_ARRIVED, $visit->status);
    }
}
