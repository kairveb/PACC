<?php

namespace Tests\Unit;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\ErVisit;
use App\Models\Patient;
use App\Models\Provider;
use App\Models\User;
use App\Services\AdmissionService;
use App\Services\AppointmentService;
use App\Services\EncounterService;
use App\Services\PatientService;
use App\Services\SchedulingService;
use App\Services\TelehealthService;
use App\Services\TriageService;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Tests\TestCase;

class SequenceGeneratorSafetyTest extends TestCase
{
    public function test_patient_generator_ignores_nonstandard_mrn_formats(): void
    {
        $user = User::factory()->create();
        $badUser = User::factory()->create();
        Patient::create([
            'mrn' => 'MRN-' . now()->format('Y') . '-000001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'date_of_birth' => '1990-01-01',
            'sex' => 'Female',
            'verified' => false,
            'user_id' => $user->id,
        ]);

        Patient::create([
            'mrn' => 'MRN-' . now()->format('Y') . '-TELEHEALTH-001',
            'first_name' => 'Bad',
            'last_name' => 'Format',
            'date_of_birth' => '1991-02-02',
            'sex' => 'Female',
            'verified' => false,
            'user_id' => $badUser->id,
        ]);

        $this->assertSame('MRN-' . now()->format('Y') . '-000002', app(PatientService::class)->generateMpn());
    }

    public function test_admission_generator_ignores_nonstandard_number_formats(): void
    {
        $user = User::factory()->create();
        $patient = Patient::create([
            'mrn' => 'MRN-' . now()->format('Y') . '-000200',
            'first_name' => 'Ann',
            'last_name' => 'Admit',
            'date_of_birth' => '1988-03-03',
            'sex' => 'Female',
            'verified' => false,
            'user_id' => $user->id,
        ]);

        Admission::create([
            'admission_number' => 'ADM-' . now()->format('Y') . '-000200',
            'patient_id' => $patient->id,
            'status' => Admission::STATUS_REQUESTED,
            'created_by' => $user->id,
        ]);

        Admission::create([
            'admission_number' => 'ADM-' . now()->format('Y') . '-TELEHEALTH-001',
            'patient_id' => $patient->id,
            'status' => Admission::STATUS_REQUESTED,
            'created_by' => $user->id,
        ]);

        $this->assertSame('ADM-' . now()->format('Y') . '-000201', app(AdmissionService::class)->generateNumber());
    }

    public function test_encounter_generator_ignores_nonstandard_number_formats(): void
    {
        $user = User::factory()->create();
        $patient = Patient::create([
            'mrn' => 'MRN-' . now()->format('Y') . '-000300',
            'first_name' => 'Eve',
            'last_name' => 'Encounter',
            'date_of_birth' => '1978-04-04',
            'sex' => 'Female',
            'verified' => false,
            'user_id' => $user->id,
        ]);
        $provider = Provider::create([
            'user_id' => $user->id,
            'department_id' => null,
            'display_name' => 'Dr. Encounter',
            'active' => true,
        ]);

        Encounter::create([
            'encounter_number' => 'ENC-' . now()->format('Y') . '-000300',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'type' => 'OUTPATIENT',
            'started_at' => now(),
            'status' => 'OPEN',
            'chief_complaint' => 'Headache',
        ]);

        Encounter::create([
            'encounter_number' => 'ENC-' . now()->format('Y') . '-TELEHEALTH-001',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'type' => 'OUTPATIENT',
            'started_at' => now(),
            'status' => 'OPEN',
            'chief_complaint' => 'Follow up',
        ]);

        $this->assertSame('ENC-' . now()->format('Y') . '-000301', app(EncounterService::class)->generateNumber());
    }

    public function test_er_visit_generator_ignores_nonstandard_number_formats(): void
    {
        $user = User::factory()->create();
        $patient = Patient::create([
            'mrn' => 'MRN-' . now()->format('Y') . '-000400',
            'first_name' => 'Fran',
            'last_name' => 'ER',
            'date_of_birth' => '1983-05-05',
            'sex' => 'Female',
            'verified' => false,
            'user_id' => $user->id,
        ]);

        ErVisit::create([
            'visit_number' => 'ER-' . now()->format('Y') . '-000400',
            'patient_id' => $patient->id,
            'arrived_at' => Carbon::now(),
            'chief_complaint' => 'Chest pain',
            'status' => ErVisit::STATUS_ARRIVED,
            'created_by' => $user->id,
        ]);

        ErVisit::create([
            'visit_number' => 'ER-' . now()->format('Y') . '-TELEHEALTH-001',
            'patient_id' => $patient->id,
            'arrived_at' => Carbon::now(),
            'chief_complaint' => 'Urgent consult',
            'status' => ErVisit::STATUS_ARRIVED,
            'created_by' => $user->id,
        ]);

        $this->assertSame('ER-' . now()->format('Y') . '-000401', app(TriageService::class, ['audit' => app(AuditLogService::class)])->generateVisitNumber());
    }

    public function test_appointment_generator_ignores_nonstandard_number_formats(): void
    {
        $user = User::factory()->create();
        $patient = Patient::create([
            'mrn' => 'MRN-' . now()->format('Y') . '-000500',
            'first_name' => 'Greg',
            'last_name' => 'App',
            'date_of_birth' => '1995-06-06',
            'sex' => 'Male',
            'verified' => false,
            'user_id' => $user->id,
        ]);
        $provider = Provider::create([
            'user_id' => $user->id,
            'department_id' => null,
            'display_name' => 'Dr. App',
            'active' => true,
        ]);

        Appointment::create([
            'appointment_number' => 'APT-' . now()->format('Y') . '-000500',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'starts_at' => Carbon::now()->addHour(),
            'ends_at' => Carbon::now()->addHour()->addMinutes(30),
            'status' => Appointment::STATUS_PENDING,
            'reason' => 'Checkup',
            'created_by' => $user->id,
        ]);

        Appointment::create([
            'appointment_number' => 'APT-' . now()->format('Y') . '-TELEHEALTH-001',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'starts_at' => Carbon::now()->addHours(2),
            'ends_at' => Carbon::now()->addHours(2)->addMinutes(30),
            'status' => Appointment::STATUS_PENDING,
            'reason' => 'Telehealth consult',
            'created_by' => $user->id,
        ]);

        $service = new AppointmentService(
            app(SchedulingService::class),
            app(AuditLogService::class),
            app(TelehealthService::class)
        );

        $this->assertSame('APT-' . now()->format('Y') . '-000501', $service->generateNumber());
    }
}
