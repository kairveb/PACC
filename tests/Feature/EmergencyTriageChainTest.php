<?php

namespace Tests\Feature;

use App\Models\ErQueue;
use App\Models\ErVisit;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Role;
use App\Models\TriageAssessment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Exercises the full intended ER workflow end-to-end:
 * Patient Triage Intake -> prefilled ER intake page -> ER visit registered
 * -> visit show page -> triage/queue confirmation -> patient appears in er_queue.
 */
class EmergencyTriageChainTest extends TestCase
{
    use RefreshDatabase;

    private function actingNurse(): User
    {
        $nurse = User::factory()->create([
            'email' => 'er.chain.' . Str::uuid() . '@example.test',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
        ]);

        $role = Role::firstOrCreate(['name' => 'nurse'], ['label' => 'Nurse']);
        $triagePermission = Permission::firstOrCreate(['name' => 'triage-patients'], ['label' => 'Triage Patients']);
        $erPermission = Permission::firstOrCreate(['name' => 'view-er'], ['label' => 'View ER']);
        $role->permissions()->syncWithoutDetaching([$triagePermission->id, $erPermission->id]);
        $nurse->roles()->syncWithoutDetaching([$role->id]);

        $this->actingAs($nurse);

        return $nurse;
    }

    public function test_full_triage_to_queue_chain_works_end_to_end(): void
    {
        $nurse = $this->actingNurse();

        $patient = Patient::create([
            'user_id' => $nurse->id,
            'mrn' => 'MRN-ER-CHAIN-' . Str::uuid()->toString(),
            'first_name' => 'Elena',
            'last_name' => 'Cruz',
            'date_of_birth' => '1993-03-11',
            'sex' => 'Female',
            'verified' => true,
        ]);

        // Step 1: "Patient Triage Intake" modal -> triage.store creates the TriageAssessment + ErVisit.
        $triageResponse = $this->from(route('triage.create'))->post(route('triage.store'), [
            'patient_id' => $patient->id,
            'chief_complaint' => 'Chest pain',
            'symptoms' => 'chest pain, shortness of breath',
            'pain_score' => 8,
            'blood_pressure' => '110/70',
            'heart_rate' => 108,
            'respiratory_rate' => 22,
            'temperature' => 37.4,
            'spo2' => 95,
            'notes' => 'Initial triage note',
            'ai_confirmed' => '1',
        ]);

        $assessment = TriageAssessment::where('patient_id', $patient->id)->firstOrFail();
        $triageResponse->assertRedirect(route('triage.er-intake', $assessment));

        // Step 2: follow the redirect to the prefilled ER intake page.
        $createResponse = $this->get(route('triage.er-intake', $assessment));
        $createResponse->assertOk();
        $createResponse->assertSee('Confirm priority & queue');
        $createResponse->assertSee('Chest pain');

        $createResponse->assertSee('Clinical assessment summary');
        $createResponse->assertSee('95');
        $createResponse->assertDontSee('name="spo2"');

        // Step 3: submit the ER intake form (as prefilled) -> emergency.store finalizes/links the visit.
        $storeResponse = $this->post(route('emergency.store'), [
            'triage_assessment_id' => $assessment->id,
            'arrived_at' => now()->format('Y-m-d\TH:i'),
            'arrival_method' => 'Walk-in',
            'referral_details' => 'Registration desk note',
        ]);

        $visit = ErVisit::where('patient_id', $patient->id)->firstOrFail();
        $storeResponse->assertSessionHasNoErrors();
        $storeResponse->assertRedirect(route('emergency.show', $visit));

        // Step 4: the visit show page must clearly present the queue-confirmation step.
        $showResponse = $this->get(route('emergency.show', $visit));
        $showResponse->assertOk();
        $showResponse->assertSee('Confirm priority to add to active queue');
        $showResponse->assertSee('Action required');

        $this->assertNull($visit->fresh()->queue);

        // Step 5: confirm priority on the visit page -> this is what actually populates er_queue.
        $triagePostResponse = $this->post(route('emergency.triage', $visit), [
            'priority' => 'Level 2',
            'notes' => 'Confirmed priority',
            'treatment_area' => 'Bay 1',
            'ai_confirmed' => '1',
        ]);

        $triagePostResponse->assertRedirect(route('emergency.show', $visit));

        $visit->refresh();
        $this->assertNotNull($visit->queue, 'Patient should now be in the active ER queue.');
        $this->assertSame('Level 2', $visit->queue->priority);
        $this->assertSame(1, ErQueue::where('er_visit_id', $visit->id)->count());
        $this->assertSame(1, TriageAssessment::where('er_visit_id', $visit->id)->count());
    }

    public function test_priority_can_be_adjusted_after_patient_is_already_queued(): void
    {
        $this->actingNurse();

        $patient = Patient::create([
            'mrn' => 'MRN-ER-OVERRIDE-' . Str::uuid()->toString(),
            'first_name' => 'Marco',
            'last_name' => 'Reyes',
            'date_of_birth' => '1979-12-02',
            'sex' => 'Male',
            'verified' => true,
        ]);

        $visit = ErVisit::create([
            'visit_number' => 'ER-TEST-' . Str::uuid()->toString(),
            'patient_id' => $patient->id,
            'arrived_at' => now(),
            'arrival_method' => 'Walk-in',
            'chief_complaint' => 'Abdominal pain',
            'status' => ErVisit::STATUS_ARRIVED,
        ]);

        $this->post(route('emergency.triage', $visit), [
            'patient_id' => $patient->id,
            'chief_complaint' => 'Abdominal pain',
            'priority' => 'Level 3',
            'ai_confirmed' => '1',
        ])->assertRedirect(route('emergency.show', $visit));

        $queuedAt = $visit->fresh()->queue->queued_at;

        // Clinical override: condition worsened, escalate the priority without duplicating the queue row.
        $this->post(route('emergency.triage', $visit), [
            'patient_id' => $patient->id,
            'chief_complaint' => 'Abdominal pain',
            'priority_override' => 'Emergency',
            'ai_confirmed' => '1',
            'notes' => 'Escalated: patient condition worsened.',
        ])->assertRedirect(route('emergency.show', $visit));

        $visit->refresh();
        $this->assertSame(1, ErQueue::where('er_visit_id', $visit->id)->count());
        $this->assertSame(1, TriageAssessment::where('er_visit_id', $visit->id)->count());
        $this->assertSame('Level 1', $visit->queue->priority);
        $this->assertTrue($queuedAt->equalTo($visit->queue->queued_at), 'Overriding priority should not reset the original queued_at time.');
    }
}
