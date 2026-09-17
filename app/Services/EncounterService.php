<?php

namespace App\Services;

use App\Models\Encounter;
use App\Models\EncounterNote;
use App\Models\Vital;
use Illuminate\Support\Facades\DB;

class EncounterService
{
    public function __construct(protected AuditLogService $audit)
    {
    }

    /**
     * Generate the next encounter number.
     */
    public function generateNumber(): string
    {
        $year = now()->format('Y');
        $candidates = Encounter::where('encounter_number', 'like', "ENC-{$year}-%")
            ->lockForUpdate()
            ->pluck('encounter_number');

        $maxSequence = 0;
        foreach ($candidates as $number) {
            if (preg_match('/^ENC-' . preg_quote($year, '/') . '-(\d{6})$/', $number, $matches)) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        return sprintf('ENC-%s-%06d', $year, $maxSequence + 1);
    }

    /**
     * Create an encounter (outpatient, telehealth, or emergency).
     */
    public function create(array $data): Encounter
    {
        return DB::transaction(function () use ($data) {
            $encounter = $this->createEncounterRecord($data);
            $this->attachOptionalVitals($encounter, $data['vitals'] ?? null);
            $this->attachOptionalNotes($encounter, $data['notes'] ?? null);
            $this->audit->createEncounter($encounter->id);

            return $encounter;
        });
    }

    protected function createEncounterRecord(array $data): Encounter
    {
        return Encounter::create([
            'encounter_number' => $this->generateNumber(),
            'patient_id' => $data['patient_id'],
            'provider_id' => $data['provider_id'],
            'appointment_id' => $data['appointment_id'] ?? null,
            'type' => $data['type'],
            'started_at' => $data['started_at'] ?? now(),
            'ended_at' => $data['ended_at'] ?? null,
            'chief_complaint' => $data['chief_complaint'] ?? null,
            'assessment' => $data['assessment'] ?? null,
            'plan' => $data['plan'] ?? null,
            'follow_up_date' => $data['follow_up_date'] ?? null,
            'status' => $data['status'] ?? 'OPEN',
        ]);
    }

    protected function attachOptionalVitals(Encounter $encounter, ?array $vitals): void
    {
        if (empty($vitals)) {
            return;
        }

        $this->recordVitals($encounter, $vitals);
    }

    protected function attachOptionalNotes(Encounter $encounter, ?string $notes): void
    {
        if (empty($notes)) {
            return;
        }

        $this->addNote($encounter, $notes, auth()->id());
    }

    public function recordVitals(Encounter $encounter, array $vitals): Vital
    {
        return Vital::create([
            'encounter_id' => $encounter->id,
            'patient_id' => $encounter->patient_id,
            'recorded_by' => auth()->id(),
            'blood_pressure' => $vitals['blood_pressure'] ?? null,
            'heart_rate' => $vitals['heart_rate'] ?? null,
            'respiratory_rate' => $vitals['respiratory_rate'] ?? null,
            'temperature' => $vitals['temperature'] ?? null,
            'spo2' => $vitals['spo2'] ?? null,
            'weight' => $vitals['weight'] ?? null,
            'pain_score' => $vitals['pain_score'] ?? null,
            'recorded_at' => now(),
        ]);
    }

    public function addNote(Encounter $encounter, string $body, ?int $authorId = null): EncounterNote
    {
        return EncounterNote::create([
            'encounter_id' => $encounter->id,
            'author_id' => $authorId ?? auth()->id(),
            'body' => $body,
        ]);
    }

    public function complete(Encounter $encounter, array $data): Encounter
    {
        $encounter->update([
            'assessment' => $data['assessment'] ?? $encounter->assessment,
            'plan' => $data['plan'] ?? $encounter->plan,
            'follow_up_date' => $data['follow_up_date'] ?? $encounter->follow_up_date,
            'ended_at' => now(),
            'status' => 'COMPLETED',
        ]);

        return $encounter;
    }
}
