<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\ErQueue;
use App\Models\ErVisit;
use App\Models\Patient;
use App\Models\Provider;
use App\Models\Role;
use App\Models\TelehealthSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmergencyAndTelehealthUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_emergency_index_page_shows_triage_dashboard_sections(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $response = $this->actingAs($user, 'web')->get('/emergency');

        $response->assertOk();
        $response->assertSee('Active ER queue');
        $response->assertSee('New ER Intake');
    }

    public function test_er_queue_can_filter_by_patient_name(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $matchingPatient = Patient::create([
            'mrn' => 'MRN-ER-FILTER-ALICE',
            'first_name' => 'Alice',
            'last_name' => 'Summers',
            'date_of_birth' => '1990-01-01',
            'sex' => 'Female',
            'phone' => '09170000011',
            'email' => 'alice@example.test',
            'verified' => true,
        ]);
        $otherPatient = Patient::create([
            'mrn' => 'MRN-ER-FILTER-BOB',
            'first_name' => 'Bob',
            'last_name' => 'Jordan',
            'date_of_birth' => '1988-02-02',
            'sex' => 'Male',
            'phone' => '09170000012',
            'email' => 'bob@example.test',
            'verified' => true,
        ]);

        $matchingVisit = ErVisit::create([
            'visit_number' => 'ER-ALICE-001',
            'patient_id' => $matchingPatient->id,
            'chief_complaint' => 'Severe chest pain',
            'arrived_at' => now(),
            'status' => 'ARRIVED',
        ]);
        $otherVisit = ErVisit::create([
            'visit_number' => 'ER-BOB-001',
            'patient_id' => $otherPatient->id,
            'chief_complaint' => 'Back pain',
            'arrived_at' => now(),
            'status' => 'ARRIVED',
        ]);

        ErQueue::create([
            'er_visit_id' => $matchingVisit->id,
            'priority' => 'Level 2',
            'status' => 'WAITING',
            'queued_at' => now(),
        ]);
        ErQueue::create([
            'er_visit_id' => $otherVisit->id,
            'priority' => 'Level 3',
            'status' => 'WAITING',
            'queued_at' => now(),
        ]);

        $response = $this->actingAs($user, 'web')->get('/emergency?q=Alice');

        $response->assertOk();
        $response->assertViewHas('queue', function ($queue) {
            $queue->load('erVisit.patient');

            return $queue->count() === 1
                && $queue->first()->erVisit->patient->full_name === 'Alice Summers'
                && $queue->pluck('erVisit.patient.full_name')->filter(fn ($name) => $name === 'Bob Jordan')->isEmpty();
        });
    }

    public function test_er_queue_can_filter_by_priority(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $levelOnePatient = Patient::create([
            'mrn' => 'MRN-ER-PRIORITY-1',
            'first_name' => 'Priority',
            'last_name' => 'One',
            'date_of_birth' => '1988-03-03',
            'sex' => 'Female',
            'phone' => '09170000021',
            'email' => 'priority-one@example.test',
            'verified' => true,
        ]);
        $levelTwoPatient = Patient::create([
            'mrn' => 'MRN-ER-PRIORITY-2',
            'first_name' => 'Priority',
            'last_name' => 'Two',
            'date_of_birth' => '1989-04-04',
            'sex' => 'Male',
            'phone' => '09170000022',
            'email' => 'priority-two@example.test',
            'verified' => true,
        ]);

        $visitOne = ErVisit::create([
            'visit_number' => 'ER-LEVEL-ONE-001',
            'patient_id' => $levelOnePatient->id,
            'chief_complaint' => 'Critical symptoms',
            'arrived_at' => now(),
            'status' => 'ARRIVED',
        ]);
        $visitTwo = ErVisit::create([
            'visit_number' => 'ER-LEVEL-TWO-001',
            'patient_id' => $levelTwoPatient->id,
            'chief_complaint' => 'Moderate pain',
            'arrived_at' => now(),
            'status' => 'ARRIVED',
        ]);

        ErQueue::create([
            'er_visit_id' => $visitOne->id,
            'priority' => 'Level 1',
            'status' => 'WAITING',
            'queued_at' => now(),
        ]);
        ErQueue::create([
            'er_visit_id' => $visitTwo->id,
            'priority' => 'Level 2',
            'status' => 'WAITING',
            'queued_at' => now(),
        ]);

        $response = $this->actingAs($user, 'web')->get('/emergency?priority=Level%201');

        $response->assertOk();
        $response->assertViewHas('queue', function ($queue) {
            $queue->load('erVisit.patient');

            return $queue->count() === 1
                && $queue->first()->erVisit->patient->full_name === 'Priority One'
                && $queue->pluck('erVisit.patient.full_name')->filter(fn ($name) => $name === 'Priority Two')->isEmpty();
        });
    }

    public function test_er_queue_can_filter_by_status(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $waitingPatient = Patient::create([
            'mrn' => 'MRN-ER-STATUS-WAITING',
            'first_name' => 'Waiting',
            'last_name' => 'Patient',
            'date_of_birth' => '1991-05-05',
            'sex' => 'Female',
            'phone' => '09170000031',
            'email' => 'waiting@example.test',
            'verified' => true,
        ]);
        $donePatient = Patient::create([
            'mrn' => 'MRN-ER-STATUS-DONE',
            'first_name' => 'Done',
            'last_name' => 'Patient',
            'date_of_birth' => '1992-06-06',
            'sex' => 'Male',
            'phone' => '09170000032',
            'email' => 'done@example.test',
            'verified' => true,
        ]);

        $waitingVisit = ErVisit::create([
            'visit_number' => 'ER-STATUS-WAITING-001',
            'patient_id' => $waitingPatient->id,
            'chief_complaint' => 'Waiting symptoms',
            'arrived_at' => now(),
            'status' => 'ARRIVED',
        ]);
        $doneVisit = ErVisit::create([
            'visit_number' => 'ER-STATUS-DONE-001',
            'patient_id' => $donePatient->id,
            'chief_complaint' => 'Resolved symptoms',
            'arrived_at' => now(),
            'status' => 'DISCHARGED',
        ]);

        ErQueue::create([
            'er_visit_id' => $waitingVisit->id,
            'priority' => 'Level 3',
            'status' => 'WAITING',
            'queued_at' => now(),
        ]);
        ErQueue::create([
            'er_visit_id' => $doneVisit->id,
            'priority' => 'Level 2',
            'status' => 'DONE',
            'queued_at' => now(),
        ]);

        $response = $this->actingAs($user, 'web')->get('/emergency?status=DONE');

        $response->assertOk();
        $response->assertViewHas('queue', function ($queue) {
            $queue->load('erVisit.patient');

            return $queue->count() === 1
                && $queue->first()->erVisit->patient->full_name === 'Done Patient'
                && $queue->pluck('erVisit.patient.full_name')->filter(fn ($name) => $name === 'Waiting Patient')->isEmpty();
        });
    }

    public function test_doctor_queue_can_filter_by_patient_name(): void
    {
        $this->seed(\Database\Seeders\HimsSeeder::class);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'doctor'], ['label' => 'Doctor']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $alice = Patient::create([
            'mrn' => 'MRN-DOCTOR-ALICE',
            'first_name' => 'Alice',
            'last_name' => 'Wong',
            'date_of_birth' => '1990-01-01',
            'sex' => 'Female',
            'phone' => '09170000041',
            'email' => 'doctor-alice@example.test',
            'verified' => true,
        ]);
        $bob = Patient::create([
            'mrn' => 'MRN-DOCTOR-BOB',
            'first_name' => 'Bob',
            'last_name' => 'Ng',
            'date_of_birth' => '1988-05-05',
            'sex' => 'Male',
            'phone' => '09170000042',
            'email' => 'doctor-bob@example.test',
            'verified' => true,
        ]);

        $nurse = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $matchingVisit = ErVisit::create([
            'visit_number' => 'ER-DOCTOR-QUEUE-ALICE',
            'patient_id' => $alice->id,
            'chief_complaint' => 'Shortness of breath',
            'arrived_at' => now(),
            'status' => 'ARRIVED',
        ]);
        $otherVisit = ErVisit::create([
            'visit_number' => 'ER-DOCTOR-QUEUE-BOB',
            'patient_id' => $bob->id,
            'chief_complaint' => 'Back pain',
            'arrived_at' => now(),
            'status' => 'ARRIVED',
        ]);

        \App\Models\TriageAssessment::create([
            'patient_id' => $alice->id,
            'er_visit_id' => $matchingVisit->id,
            'triage_nurse_id' => $nurse->id,
            'chief_complaint' => 'Shortness of breath',
            'triaged_at' => now(),
            'status' => 'WAITING',
            'priority' => 'Emergency',
            'priority_score' => 1,
            'pain_score' => 8,
        ]);
        \App\Models\TriageAssessment::create([
            'patient_id' => $bob->id,
            'er_visit_id' => $otherVisit->id,
            'triage_nurse_id' => $nurse->id,
            'chief_complaint' => 'Back pain',
            'triaged_at' => now(),
            'status' => 'WAITING',
            'priority' => 'Urgent',
            'priority_score' => 2,
            'pain_score' => 6,
        ]);

        $response = $this->actingAs($user, 'web')->get('/doctors/queue?q=Alice');

        $response->assertOk();
        $response->assertViewHas('queue', function ($queue) {
            return $queue->count() === 1
                && $queue->first()->patient->full_name === 'Alice Wong'
                && $queue->pluck('patient.full_name')->filter(fn ($name) => $name === 'Bob Ng')->isEmpty();
        });
    }

    public function test_doctor_queue_can_filter_by_priority(): void
    {
        $this->seed(\Database\Seeders\HimsSeeder::class);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'doctor'], ['label' => 'Doctor']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $priorityOnePatient = Patient::create([
            'mrn' => 'MRN-DOCTOR-PRIORITY-1',
            'first_name' => 'Priority',
            'last_name' => 'One',
            'date_of_birth' => '1984-07-07',
            'sex' => 'Female',
            'phone' => '09170000051',
            'email' => 'doctor-priority-one@example.test',
            'verified' => true,
        ]);
        $priorityTwoPatient = Patient::create([
            'mrn' => 'MRN-DOCTOR-PRIORITY-2',
            'first_name' => 'Priority',
            'last_name' => 'Two',
            'date_of_birth' => '1989-08-08',
            'sex' => 'Male',
            'phone' => '09170000052',
            'email' => 'doctor-priority-two@example.test',
            'verified' => true,
        ]);

        $nurse = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $priorityOneVisit = ErVisit::create([
            'visit_number' => 'ER-DOCTOR-PRIORITY-ONE',
            'patient_id' => $priorityOnePatient->id,
            'chief_complaint' => 'Chest pain',
            'arrived_at' => now(),
            'status' => 'ARRIVED',
        ]);
        $priorityTwoVisit = ErVisit::create([
            'visit_number' => 'ER-DOCTOR-PRIORITY-TWO',
            'patient_id' => $priorityTwoPatient->id,
            'chief_complaint' => 'Leg swelling',
            'arrived_at' => now(),
            'status' => 'ARRIVED',
        ]);

        \App\Models\TriageAssessment::create([
            'patient_id' => $priorityOnePatient->id,
            'er_visit_id' => $priorityOneVisit->id,
            'triage_nurse_id' => $nurse->id,
            'chief_complaint' => 'Chest pain',
            'triaged_at' => now(),
            'status' => 'WAITING',
            'priority' => 'Emergency',
            'priority_score' => 1,
            'pain_score' => 9,
        ]);
        \App\Models\TriageAssessment::create([
            'patient_id' => $priorityTwoPatient->id,
            'er_visit_id' => $priorityTwoVisit->id,
            'triage_nurse_id' => $nurse->id,
            'chief_complaint' => 'Leg swelling',
            'triaged_at' => now(),
            'status' => 'WAITING',
            'priority' => 'Urgent',
            'priority_score' => 2,
            'pain_score' => 5,
        ]);

        $response = $this->actingAs($user, 'web')->get('/doctors/queue?priority=1');

        $response->assertOk();
        $response->assertViewHas('queue', function ($queue) {
            return $queue->count() === 1
                && $queue->first()->patient->full_name === 'Priority One'
                && $queue->pluck('patient.full_name')->filter(fn ($name) => $name === 'Priority Two')->isEmpty();
        });
    }

    public function test_admissions_index_can_filter_by_status(): void
    {
        $this->seed(\Database\Seeders\HimsSeeder::class);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'hospital-admin'], ['label' => 'Hospital Admin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $requestedPatient = Patient::create([
            'mrn' => 'MRN-ADMISSION-REQUESTED',
            'first_name' => 'Requested',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-06-06',
            'sex' => 'Female',
            'phone' => '09170000061',
            'email' => 'requested@example.test',
            'verified' => true,
        ]);
        $admittedPatient = Patient::create([
            'mrn' => 'MRN-ADMISSION-ADMITTED',
            'first_name' => 'Admitted',
            'last_name' => 'Patient',
            'date_of_birth' => '1987-11-11',
            'sex' => 'Male',
            'phone' => '09170000062',
            'email' => 'admitted@example.test',
            'verified' => true,
        ]);

        \App\Models\Admission::create([
            'admission_number' => 'ADM-REQ-001',
            'patient_id' => $requestedPatient->id,
            'reason' => 'Observation',
            'status' => \App\Models\Admission::STATUS_REQUESTED,
            'created_by' => $user->id,
            'admitted_at' => null,
        ]);
        \App\Models\Admission::create([
            'admission_number' => 'ADM-ADT-001',
            'patient_id' => $admittedPatient->id,
            'reason' => 'Inpatient care',
            'status' => \App\Models\Admission::STATUS_ADMITTED,
            'created_by' => $user->id,
            'admitted_at' => now(),
        ]);

        $response = $this->actingAs($user, 'web')->get('/admissions?status=' . \App\Models\Admission::STATUS_REQUESTED);

        $response->assertOk();
        $response->assertViewHas('admissions', function ($admissions) {
            return $admissions->count() === 1
                && $admissions->first()->patient->full_name === 'Requested Patient'
                && $admissions->pluck('patient.full_name')->filter(fn ($name) => $name === 'Admitted Patient')->isEmpty();
        });
    }

    public function test_telehealth_index_page_shows_session_controls_and_stats(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $response = $this->actingAs($user, 'web')->get('/telehealth');

        $response->assertOk();
        $response->assertSee('Session controls');
        $response->assertSee('Launch video call');
    }

    public function test_telehealth_show_page_exposes_closeout_summary_form(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $patient = Patient::create([
            'mrn' => 'MRN-CLOSEOUT-001',
            'first_name' => 'Closeout',
            'last_name' => 'Patient',
            'date_of_birth' => '1990-01-01',
            'sex' => 'Female',
            'phone' => '09170000011',
            'email' => 'closeout@example.test',
            'verified' => true,
        ]);
        $provider = Provider::create([
            'user_id' => User::factory()->create()->id,
            'display_name' => 'Dr. Summary',
            'active' => true,
        ]);
        $appointmentType = AppointmentType::create([
            'name' => 'Telehealth',
            'default_duration' => 30,
            'telehealth' => true,
        ]);
        $appointment = Appointment::create([
            'appointment_number' => 'APT-CLOSEOUT-001',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'appointment_type_id' => $appointmentType->id,
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(30),
            'status' => 'CONFIRMED',
        ]);
        $session = TelehealthSession::create([
            'appointment_id' => $appointment->id,
            'start_time' => now(),
            'duration' => 30,
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($user, 'web')->get('/telehealth/' . $session->id);

        $response->assertOk();
        $response->assertSee('Closeout consultation');
        $response->assertSee('Clinical summary');
    }

    public function test_patient_show_page_surfaces_latest_telehealth_summary(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $patient = Patient::create([
            'mrn' => 'MRN-SUMMARY-001',
            'first_name' => 'Summary',
            'last_name' => 'Patient',
            'date_of_birth' => '1988-08-08',
            'sex' => 'Male',
            'phone' => '09170000022',
            'email' => 'summary@example.test',
            'verified' => true,
        ]);
        $provider = Provider::create([
            'user_id' => User::factory()->create()->id,
            'display_name' => 'Dr. Chart',
            'active' => true,
        ]);
        $appointmentType = AppointmentType::create([
            'name' => 'Telehealth',
            'default_duration' => 30,
            'telehealth' => true,
        ]);
        $appointment = Appointment::create([
            'appointment_number' => 'APT-SUMMARY-001',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'appointment_type_id' => $appointmentType->id,
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(30),
            'status' => 'COMPLETED',
        ]);
        $encounter = $patient->encounters()->create([
            'appointment_id' => $appointment->id,
            'encounter_number' => 'ENC-2026-000001',
            'provider_id' => $provider->id,
            'type' => 'TELEHEALTH',
            'started_at' => now(),
            'assessment' => 'Symptoms improved with treatment.',
            'plan' => 'Follow up in 7 days if symptoms persist.',
            'discharge_instructions' => 'Rest and continue hydration; return if symptoms worsen.',
            'follow_up_date' => now()->addDays(7)->toDateString(),
            'status' => 'COMPLETED',
        ]);

        $response = $this->actingAs($user, 'web')->get('/patients/' . $patient->id);

        $response->assertOk();
        $response->assertSee('Latest consultation summary');
        $response->assertSee('Symptoms improved with treatment.');
        $response->assertSee('Rest and continue hydration; return if symptoms worsen.');
        $response->assertSee('Follow-up');
        $response->assertSee(route('encounters.show', $encounter));
    }

    public function test_encounter_completion_posts_to_registered_route(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $patient = Patient::create([
            'mrn' => 'MRN-ENCOUNTER-001',
            'first_name' => 'Encounter',
            'last_name' => 'Patient',
            'date_of_birth' => '1992-02-02',
            'sex' => 'Male',
            'phone' => '09170000123',
            'email' => 'encounter@example.test',
            'verified' => true,
        ]);
        $provider = Provider::create([
            'user_id' => User::factory()->create()->id,
            'display_name' => 'Dr. Completion',
            'active' => true,
        ]);
        $encounter = $patient->encounters()->create([
            'encounter_number' => 'ENC-2026-000500',
            'provider_id' => $provider->id,
            'type' => 'OUTPATIENT',
            'started_at' => now(),
            'status' => 'OPEN',
        ]);

        $response = $this->actingAs($user, 'web')->post(route('encounters.complete', $encounter), [
            'assessment' => 'Updated assessment',
            'plan' => 'Follow up in 7 days',
            'follow_up_date' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('encounters', [
            'id' => $encounter->id,
            'status' => 'COMPLETED',
            'assessment' => 'Updated assessment',
        ]);
    }

    public function test_outpatient_encounters_can_filter_by_patient_name_type_and_status(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $matchingPatient = Patient::create([
            'mrn' => 'MRN-OUTPATIENT-FILTER-ALICE',
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
            'date_of_birth' => '1988-07-15',
            'sex' => 'Female',
            'phone' => '09170000141',
            'email' => 'alice-encounter@example.test',
            'verified' => true,
        ]);
        $otherPatient = Patient::create([
            'mrn' => 'MRN-OUTPATIENT-FILTER-BOB',
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'date_of_birth' => '1979-04-20',
            'sex' => 'Male',
            'phone' => '09170000142',
            'email' => 'bob-encounter@example.test',
            'verified' => true,
        ]);
        $provider = Provider::create([
            'user_id' => User::factory()->create()->id,
            'display_name' => 'Dr. Filter',
            'active' => true,
        ]);

        $matchingEncounter = $matchingPatient->encounters()->create([
            'encounter_number' => 'ENC-2026-000700',
            'provider_id' => $provider->id,
            'type' => 'OUTPATIENT',
            'started_at' => now()->subHour(),
            'status' => 'OPEN',
        ]);
        $otherEncounter = $otherPatient->encounters()->create([
            'encounter_number' => 'ENC-2026-000701',
            'provider_id' => $provider->id,
            'type' => 'TELEHEALTH',
            'started_at' => now()->subMinutes(30),
            'status' => 'COMPLETED',
        ]);

        $response = $this->actingAs($user, 'web')->get('/outpatient?q=Alice&type=OUTPATIENT&status=OPEN');

        $response->assertOk();
        $response->assertViewHas('encounters', function ($encounters) use ($matchingEncounter, $otherEncounter) {
            $encounters->load('patient');

            return $encounters->count() === 1
                && $encounters->first()->id === $matchingEncounter->id
                && $encounters->pluck('patient.full_name')->filter(fn ($name) => $name === 'Bob Jones')->isEmpty();
        });
    }

    public function test_dashboard_surfaces_follow_up_due_patients(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $patient = Patient::create([
            'mrn' => 'MRN-FOLLOWUP-001',
            'first_name' => 'Follow',
            'last_name' => 'Up',
            'date_of_birth' => '1985-05-05',
            'sex' => 'Female',
            'phone' => '09170000099',
            'email' => 'followup@example.test',
            'verified' => true,
        ]);
        $provider = Provider::create([
            'user_id' => User::factory()->create()->id,
            'display_name' => 'Dr. Dashboard',
            'active' => true,
        ]);
        $appointment = Appointment::create([
            'appointment_number' => 'APT-FOLLOWUP-001',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addMinutes(30),
            'status' => 'CONFIRMED',
        ]);
        $patient->encounters()->create([
            'appointment_id' => $appointment->id,
            'encounter_number' => 'ENC-2026-000200',
            'provider_id' => $provider->id,
            'type' => 'TELEHEALTH',
            'started_at' => now()->subDays(2),
            'assessment' => 'Improving after treatment.',
            'plan' => 'Recheck in one week.',
            'follow_up_date' => now()->addDays(2)->toDateString(),
            'status' => 'COMPLETED',
        ]);

        $response = $this->actingAs($user, 'web')->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Follow-up due');
        $response->assertSee('Follow Up');
        $response->assertSee('/appointments?follow_up=due', false);
    }

    public function test_appointment_list_shows_follow_up_indicator(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $patient = Patient::create([
            'mrn' => 'MRN-APPT-FOLLOWUP-001',
            'first_name' => 'Schedule',
            'last_name' => 'Review',
            'date_of_birth' => '1987-06-06',
            'sex' => 'Male',
            'phone' => '09170000111',
            'email' => 'schedule-review@example.test',
            'verified' => true,
        ]);
        $provider = Provider::create([
            'user_id' => User::factory()->create()->id,
            'display_name' => 'Dr. Schedule',
            'active' => true,
        ]);
        $appointment = Appointment::create([
            'appointment_number' => 'APT-REVIEW-001',
            'patient_id' => $patient->id,
            'provider_id' => $provider->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addMinutes(30),
            'status' => 'CONFIRMED',
        ]);
        $patient->encounters()->create([
            'appointment_id' => $appointment->id,
            'encounter_number' => 'ENC-2026-001000',
            'provider_id' => $provider->id,
            'type' => 'TELEHEALTH',
            'started_at' => now()->subDays(3),
            'assessment' => 'Symptoms settling.',
            'plan' => 'Check again in 5 days.',
            'follow_up_date' => now()->addDays(5)->toDateString(),
            'status' => 'COMPLETED',
        ]);

        $response = $this->actingAs($user, 'web')->get('/appointments');

        $response->assertOk();
        $response->assertSee('Follow-up');
        $response->assertSee('Schedule Review');
    }

    public function test_appointment_list_can_filter_follow_up_due_patients(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $role = Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $duePatient = Patient::create([
            'mrn' => 'MRN-FILTER-DUE-001',
            'first_name' => 'Due',
            'last_name' => 'Patient',
            'date_of_birth' => '1992-02-02',
            'sex' => 'Female',
            'phone' => '09170000123',
            'email' => 'due-filter@example.test',
            'verified' => true,
        ]);
        $otherPatient = Patient::create([
            'mrn' => 'MRN-FILTER-LATER-001',
            'first_name' => 'Later',
            'last_name' => 'Patient',
            'date_of_birth' => '1993-03-03',
            'sex' => 'Male',
            'phone' => '09170000124',
            'email' => 'later-filter@example.test',
            'verified' => true,
        ]);
        $provider = Provider::create([
            'user_id' => User::factory()->create()->id,
            'display_name' => 'Dr. Filter',
            'active' => true,
        ]);

        $dueAppointment = Appointment::create([
            'appointment_number' => 'APT-FILTER-DUE-001',
            'patient_id' => $duePatient->id,
            'provider_id' => $provider->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addMinutes(30),
            'status' => 'CONFIRMED',
        ]);
        $laterAppointment = Appointment::create([
            'appointment_number' => 'APT-FILTER-LATER-001',
            'patient_id' => $otherPatient->id,
            'provider_id' => $provider->id,
            'starts_at' => now()->addDays(10),
            'ends_at' => now()->addDays(10)->addMinutes(30),
            'status' => 'CONFIRMED',
        ]);

        $duePatient->encounters()->create([
            'appointment_id' => $dueAppointment->id,
            'encounter_number' => 'ENC-2026-010000',
            'provider_id' => $provider->id,
            'type' => 'TELEHEALTH',
            'started_at' => now()->subDays(3),
            'assessment' => 'Better but still needs a review.',
            'plan' => 'See again soon.',
            'follow_up_date' => now()->addDays(2)->toDateString(),
            'status' => 'COMPLETED',
        ]);
        $otherPatient->encounters()->create([
            'appointment_id' => $laterAppointment->id,
            'encounter_number' => 'ENC-2026-010001',
            'provider_id' => $provider->id,
            'type' => 'TELEHEALTH',
            'started_at' => now()->subDays(3),
            'assessment' => 'Stable after last visit.',
            'plan' => 'Follow-up in two weeks.',
            'follow_up_date' => now()->addDays(20)->toDateString(),
            'status' => 'COMPLETED',
        ]);

        $response = $this->actingAs($user, 'web')->get('/appointments?follow_up=due');

        $response->assertOk();
        $response->assertSee('Due Patient');
        $response->assertDontSee('Later Patient');
    }
}
