<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PreArrivalProfile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FieldLevelEncryptionTest extends TestCase
{
    public function test_sensitive_fields_are_encrypted_at_rest_and_readable_through_the_model(): void
    {
        $patient = Patient::create([
            'mrn' => 'MRN-2026-0001',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'date_of_birth' => '1990-01-15',
            'sex' => 'Female',
            'allergies' => 'Penicillin allergy',
            'insurance_number' => 'ABC-123456',
            'verified' => false,
        ]);

        $patientRow = DB::table('patients')->where('id', $patient->id)->first();

        $this->assertNotSame('Penicillin allergy', $patientRow->allergies);
        $this->assertNotSame('ABC-123456', $patientRow->insurance_number);
        $this->assertSame('Penicillin allergy', $patient->fresh()->allergies);
        $this->assertSame('ABC-123456', $patient->fresh()->insurance_number);

        $profile = PreArrivalProfile::create([
            'patient_id' => $patient->id,
            'token' => 'PRE-ABC-0001',
            'status' => 'pending',
            'medical_history' => 'Asthma',
            'current_medications' => 'Ibuprofen 200mg',
            'allergies' => 'Peanut allergy',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '09171234567',
            'emergency_contact_relationship' => 'Sister',
            'contact_phone' => '09181234567',
            'contact_email' => 'alice@example.com',
        ]);

        $profileRow = DB::table('pre_arrival_profiles')->where('id', $profile->id)->first();

        $this->assertNotSame('Asthma', $profileRow->medical_history);
        $this->assertNotSame('Ibuprofen 200mg', $profileRow->current_medications);
        $this->assertNotSame('Peanut allergy', $profileRow->allergies);
        $this->assertSame('Asthma', $profile->fresh()->medical_history);
        $this->assertSame('Ibuprofen 200mg', $profile->fresh()->current_medications);
        $this->assertSame('Peanut allergy', $profile->fresh()->allergies);
    }
}
