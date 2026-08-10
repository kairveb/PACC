<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Provider;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\EncounterService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class EncounterServiceCreateTest extends TestCase
{
    public function test_create_persists_an_encounter_and_audit_log(): void
    {
        $audit = Mockery::mock(AuditLogService::class);
        $audit->shouldReceive('createEncounter')->once()->andReturnUsing(fn ($id) => new AuditLog([
            'user_id' => null,
            'action' => 'CREATE_ENCOUNTER',
            'resource_type' => 'encounter',
            'resource_id' => $id,
            'result' => 'SUCCESS',
        ]));

        $service = new EncounterService($audit);

        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        $user = User::factory()->create();
        $patient = Patient::create([
            'mrn' => 'MRN-2026-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'first_name' => 'Alan',
            'last_name' => 'Turing',
            'date_of_birth' => '1912-06-23',
            'sex' => 'Male',
            'verified' => false,
            'user_id' => $user->id,
        ]);
        $provider = Provider::create([
            'user_id' => $user->id,
            'department_id' => null,
            'display_name' => 'Dr. Turing',
            'active' => true,
        ]);

        $encounter = $service->create([
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'type' => 'OUTPATIENT',
            'chief_complaint' => 'Headache',
            'status' => 'OPEN',
        ]);

        $this->assertInstanceOf(Encounter::class, $encounter);
        $this->assertSame($patient->id, $encounter->patient_id);
        $this->assertSame($provider->id, $encounter->provider_id);
        $this->assertSame('OPEN', $encounter->status);
    }
}
