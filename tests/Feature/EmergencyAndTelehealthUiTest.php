<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentType;
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
