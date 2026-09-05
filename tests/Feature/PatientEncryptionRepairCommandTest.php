<?php

namespace Tests\Feature;

use App\Models\Patient;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PatientEncryptionRepairCommandTest extends TestCase
{
    public function test_repair_command_reencrypts_legacy_plaintext_patient_fields(): void
    {
        $patientId = DB::table('patients')->insertGetId([
            'mrn' => 'MRN-LEGACY-REPAIR-1',
            'first_name' => 'Legacy',
            'last_name' => 'Patient',
            'date_of_birth' => '1985-02-14',
            'sex' => 'Female',
            'allergies' => 'Dust allergy',
            'insurance_number' => 'LEGACY-123',
            'verified' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rawBefore = DB::table('patients')->where('id', $patientId)->first();
        $this->assertSame('Dust allergy', $rawBefore->allergies);
        $this->assertSame('LEGACY-123', $rawBefore->insurance_number);

        Artisan::call('patients:repair-encryption');

        $patient = Patient::find($patientId);
        $this->assertNotNull($patient);
        $this->assertSame('Dust allergy', $patient->allergies);
        $this->assertSame('LEGACY-123', $patient->insurance_number);

        $rawAfter = DB::table('patients')->where('id', $patientId)->first();
        $this->assertNotSame('Dust allergy', $rawAfter->allergies);
        $this->assertNotSame('LEGACY-123', $rawAfter->insurance_number);
    }
}
