<?php

namespace App\Services;

use App\Models\ErQueue;
use App\Models\ErVisit;
use App\Models\TriageAssessment;
use App\Models\TriageVital;
use Illuminate\Support\Facades\DB;

class TriageService
{
    private const IMMEDIATE_THREAT_TERMS = [
        'cardiac arrest',
        'respiratory arrest',
        'pulseless',
        'airway obstruction',
        'major uncontrolled hemorrhage',
        'severe bleeding',
        'unresponsive',
        'apnea',
        'severe trauma',
    ];

    private const HIGH_RISK_TERMS = [
        'stroke',
        'chest pain',
        'severe breathing difficulty',
        'difficulty breathing',
        'shortness of breath',
        'altered mental status',
        'sepsis',
        'suicidal',
        'homicidal',
        'intoxication',
        'agitation',
    ];

    private const MULTIPLE_RESOURCE_TERMS = [
        'abdominal pain',
        'high fever',
        'cough',
        'headache',
        'trauma',
        'fracture',
        'vomiting',
        'diarrhea',
        'dizziness',
        'syncope',
        'infection',
    ];

    private const SINGLE_RESOURCE_TERMS = [
        'laceration',
        'sprain',
        'minor rash',
        'medication refill',
        'sore throat',
        'simple wound',
        'burn',
        'itching',
        'rash',
        'stitch',
        'suture',
        'x-ray',
    ];

    public function __construct(protected AuditLogService $audit)
    {
    }

    /**
     * Generate the next ER visit number.
     */
    public function generateVisitNumber(): string
    {
        $year = now()->format('Y');
        $last = ErVisit::where('visit_number', 'like', "ER-{$year}-%")
            ->orderByDesc('visit_number')
            ->lockForUpdate()
            ->first();

        if ($last) {
            $parts = explode('-', $last->visit_number);
            return "ER-{$year}-" . str_pad((string) ((int) $parts[2]) + 1, 6, '0', STR_PAD_LEFT);
        }

        return "ER-{$year}-000001";
    }

    /**
     * Register an ER visit (arrival).
     */
    public function registerErVisit(array $data): ErVisit
    {
        return DB::transaction(function () use ($data) {
            $visit = $this->createErVisitRecord($data);
            $this->audit->createErVisit($visit->id);

            return $visit;
        });
    }

    protected function createErVisitRecord(array $data): ErVisit
    {
        return ErVisit::create([
            'visit_number' => $this->generateVisitNumber(),
            'patient_id' => $data['patient_id'],
            'arrived_at' => $data['arrived_at'] ?? now(),
            'arrival_method' => $data['arrival_method'] ?? null,
            'chief_complaint' => $data['chief_complaint'],
            'referral_details' => $data['referral_details'] ?? null,
            'status' => ErVisit::STATUS_ARRIVED,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Perform a triage assessment on an ER visit. Clinical priority is set by the nurse.
     */
    public function triage(ErVisit $visit, array $data): TriageAssessment
    {
        return DB::transaction(function () use ($visit, $data) {
            $assessment = $this->createAssessment($visit, $data);
            $this->createVitalsIfPresent($assessment, $data['vitals'] ?? null);
            $this->markVisitTriaged($visit);
            $this->createQueueEntry($visit, $data);
            $this->audit->createTriage($assessment->id);

            return $assessment;
        });
    }

    public function score(array $data): array
    {
        $ai = app(AiTriageService::class)->analyze($data);

        return [
            'level' => $ai['level'],
            'score' => $ai['score'],
            'priority' => $ai['priority'],
            'color' => $ai['color'],
            'confidence' => $ai['confidence'],
            'recommendation' => $ai['recommendation'],
            'reasons' => $ai['reasons'],
        ];
    }

    protected function isImmediateThreat(array $values, string $complaint, array $visual, array $symptoms): bool
    {
        if ($this->containsAnyKeyword($complaint, self::IMMEDIATE_THREAT_TERMS)) {
            return true;
        }

        if ($this->anySymptomMatches($symptoms, self::IMMEDIATE_THREAT_TERMS)) {
            return true;
        }

        if (in_array($visual['breathing'] ?? '', ['Unable to speak', 'Gasping'], true)) {
            return true;
        }

        if (($visual['consciousness'] ?? '') === 'Unresponsive') {
            return true;
        }

        if (($values['vitals']['spo2'] ?? null) !== null && $values['vitals']['spo2'] < 90) {
            return true;
        }

        if (($values['vitals']['blood_pressure'] ?? null) !== null && $values['vitals']['blood_pressure'] < 90) {
            return true;
        }

        if (($values['vitals']['respiratory_rate'] ?? null) !== null && ($values['vitals']['respiratory_rate'] > 30 || $values['vitals']['respiratory_rate'] < 8)) {
            return true;
        }

        if (($values['vitals']['heart_rate'] ?? null) !== null && ($values['vitals']['heart_rate'] > 130 || $values['vitals']['heart_rate'] < 40)) {
            return true;
        }

        return false;
    }

    protected function isHighRisk(array $values, string $complaint, array $visual, int $pain, array $symptoms): bool
    {
        if ($this->containsAnyKeyword($complaint, self::HIGH_RISK_TERMS)) {
            return true;
        }

        if ($this->anySymptomMatches($symptoms, self::HIGH_RISK_TERMS)) {
            return true;
        }

        if (($visual['consciousness'] ?? '') === 'Drowsy') {
            return true;
        }

        if ($pain >= 8 && $this->containsAnyKeyword($complaint, ['chest pain', 'abdominal pain', 'headache', 'breathing', 'trauma'])) {
            return true;
        }

        if (($values['vitals']['temperature'] ?? null) !== null && ($values['vitals']['blood_pressure'] ?? null) !== null && $values['vitals']['temperature'] >= 38 && $values['vitals']['blood_pressure'] < 100) {
            return true;
        }

        if (($values['vitals']['spo2'] ?? null) !== null && $values['vitals']['spo2'] < 94 && ($values['vitals']['respiratory_rate'] ?? null) !== null && $values['vitals']['respiratory_rate'] > 24) {
            return true;
        }

        return false;
    }

    protected function estimateResourceCount(array $values, string $complaint, array $visual, array $symptoms): int
    {
        if ($this->containsAnyKeyword($complaint, self::MULTIPLE_RESOURCE_TERMS) || $this->anySymptomMatches($symptoms, self::MULTIPLE_RESOURCE_TERMS)) {
            return 2;
        }

        if ($this->containsAnyKeyword($complaint, self::SINGLE_RESOURCE_TERMS) || $this->anySymptomMatches($symptoms, self::SINGLE_RESOURCE_TERMS)) {
            return 1;
        }

        if (($visual['breathing'] ?? '') === 'Labored') {
            return 2;
        }

        if (($values['pain_score'] ?? 0) >= 7) {
            return 1;
        }

        return 0;
    }

    protected function containsAnyKeyword(string $text, array $keywords): bool
    {
        $text = strtolower($text);
        foreach ($keywords as $keyword) {
            if ($keyword !== '' && str_contains($text, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    protected function anySymptomMatches(array $symptoms, array $keywords): bool
    {
        foreach ($symptoms as $symptom) {
            if ($this->containsAnyKeyword((string) $symptom, $keywords)) {
                return true;
            }
        }

        return false;
    }

    protected function createAssessment(ErVisit $visit, array $data): TriageAssessment
    {
        return TriageAssessment::create([
            'patient_id' => $visit->patient_id,
            'er_visit_id' => $visit->id,
            'triage_nurse_id' => auth()->id(),
            'triaged_at' => now(),
            'chief_complaint' => $data['chief_complaint'] ?? $visit->chief_complaint,
            'pain_score' => $data['pain_score'] ?? null,
            'priority' => $data['priority'],
            'priority_score' => $this->normalizePriorityScore($data['priority']),
            'triage_color' => $this->normalizePriorityColor($data['priority']),
            'notes' => $data['notes'] ?? null,
            'status' => 'COMPLETE',
        ]);
    }

    protected function createVitalsIfPresent(TriageAssessment $assessment, ?array $vitals): void
    {
        if (empty($vitals)) {
            return;
        }

        TriageVital::create(array_merge([
            'triage_assessment_id' => $assessment->id,
            'patient_id' => $assessment->patient_id,
            'recorded_at' => now(),
        ], $vitals));
    }

    protected function markVisitTriaged(ErVisit $visit): void
    {
        $visit->update(['status' => ErVisit::STATUS_TRIAGED]);
    }

    protected function createQueueEntry(ErVisit $visit, array $data): void
    {
        ErQueue::create([
            'er_visit_id' => $visit->id,
            'priority' => $data['priority'],
            'status' => ErQueue::STATUS_WAITING,
            'treatment_area' => $data['treatment_area'] ?? null,
            'provider_id' => $data['provider_id'] ?? null,
            'queued_at' => now(),
        ]);
    }

    /**
     * Update ER queue status (e.g., IN_TREATMENT, DONE).
     */
    public function updateQueueStatus(ErQueue $queue, string $status, ?int $providerId = null): ErQueue
    {
        $queue->update([
            'status' => $status,
            'provider_id' => $providerId ?? $queue->provider_id,
        ]);

        if ($status === ErQueue::STATUS_IN_TREATMENT) {
            $this->markVisitInTreatment($queue->erVisit);
        }

        return $queue;
    }

    protected function markVisitInTreatment(ErVisit $visit): void
    {
        $visit->update(['status' => ErVisit::STATUS_IN_TREATMENT]);
    }

    protected function normalizePriorityScore(string $priority): int
    {
        return match (strtolower(trim($priority))) {
            'level 1', 'emergency', 'red' => 1,
            'level 2', 'urgent', 'yellow' => 2,
            'level 3', 'prompt', 'orange' => 3,
            'level 4', 'non-urgent', 'green' => 4,
            'level 5', 'routine' => 5,
            default => 5,
        };
    }

    protected function normalizePriorityColor(string $priority): string
    {
        return match (strtolower(trim($priority))) {
            'level 1', 'emergency', 'red' => 'red',
            'level 2', 'urgent', 'yellow' => 'yellow',
            'level 3', 'prompt', 'orange' => 'orange',
            default => 'green',
        };
    }
}
