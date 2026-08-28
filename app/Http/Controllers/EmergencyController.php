<?php

namespace App\Http\Controllers;

use App\Models\ErQueue;
use App\Models\ErVisit;
use App\Models\Patient;
use App\Models\Provider;
use App\Models\TriageAssessment;
use App\Services\TriageService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmergencyController extends Controller
{
    public function __construct(protected TriageService $triage)
    {
    }

    public function index()
    {
        $queue = ErQueue::with(['erVisit.patient', 'provider'])
            ->orderByRaw("CASE priority
                WHEN 'Level 1' THEN 1
                WHEN 'Level 2' THEN 2
                WHEN 'Level 3' THEN 3
                WHEN 'Level 4' THEN 4
                WHEN 'Level 5' THEN 5
                ELSE 99
            END")
            ->orderBy('queued_at')
            ->paginate(20);

        $visits = ErVisit::with('patient')->orderBy('arrived_at', 'desc')->limit(10)->get();
        $patients = Patient::orderBy('last_name')->get();

        return view('emergency.index', compact('queue', 'visits', 'patients'));
    }

    public function create(Request $request)
    {
        $patients = Patient::orderBy('last_name')->get();

        $prefill = [
            'triage_assessment_id' => null,
            'patient_id' => null,
            'chief_complaint' => null,
            'symptoms' => null,
            'pain_score' => null,
            'blood_pressure' => null,
            'heart_rate' => null,
            'respiratory_rate' => null,
            'temperature' => null,
            'spo2' => null,
            'referral_details' => null,
            'arrival_method' => null,
            'arrived_at' => now()->format('Y-m-d\TH:i'),
        ];

        $assessmentId = $request->query('triage_assessment_id', $request->input('triage_assessment_id'));
        if ($assessmentId) {
            $assessment = TriageAssessment::with(['patient', 'vitals'])->find($assessmentId);
            if ($assessment) {
                $prefill['triage_assessment_id'] = $assessment->id;
                $prefill['patient_id'] = $assessment->patient_id;
                $prefill['chief_complaint'] = $assessment->chief_complaint ?? null;
                $prefill['symptoms'] = is_array($assessment->symptoms) ? implode(', ', $assessment->symptoms) : $assessment->symptoms;
                $prefill['pain_score'] = $assessment->pain_score ?? null;
                $prefill['blood_pressure'] = $assessment->vitals?->blood_pressure ?? null;
                $prefill['heart_rate'] = $assessment->vitals?->heart_rate ?? null;
                $prefill['respiratory_rate'] = $assessment->vitals?->respiratory_rate ?? null;
                $prefill['temperature'] = $assessment->vitals?->temperature ?? null;
                $prefill['spo2'] = $assessment->vitals?->spo2 ?? null;
                $prefill['referral_details'] = $assessment->notes ?? null;
                $prefill['arrival_method'] = $assessment->erVisit?->arrival_method ?? null;
                $prefill['arrived_at'] = $assessment->erVisit?->arrived_at ? $assessment->erVisit->arrived_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
            }
        }

        return view('emergency.create', compact('patients', 'prefill'));
    }

    public function createFromTriage(TriageAssessment $triageAssessment)
    {
        $request = new Request(['triage_assessment_id' => $triageAssessment->id]);

        return $this->create($request);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'triage_assessment_id' => ['nullable', 'exists:triage_assessments,id'],
            'patient_id' => ['required', 'exists:patients,id'],
            'arrived_at' => ['nullable', 'date'],
            'arrival_method' => ['nullable', 'string', 'max:50'],
            'chief_complaint' => ['required', 'string'],
            'symptoms' => ['nullable', 'string'],
            'pain_score' => ['nullable', 'integer', 'min:0', 'max:10'],
            'blood_pressure' => ['nullable', 'string', 'max:20'],
            'heart_rate' => ['nullable', 'integer', 'min:0', 'max:220'],
            'respiratory_rate' => ['nullable', 'integer', 'min:0', 'max:80'],
            'temperature' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'spo2' => ['nullable', 'integer', 'min:0', 'max:100'],
            'referral_details' => ['nullable', 'string'],
        ]);

        $assessment = null;
        if (! empty($data['triage_assessment_id'])) {
            $assessment = TriageAssessment::with(['vitals', 'erVisit'])->findOrFail($data['triage_assessment_id']);

            if ($assessment->patient_id && $assessment->patient_id !== (int) $data['patient_id']) {
                $data['patient_id'] = $assessment->patient_id;
            }
        }

        $visit = $assessment?->erVisit;
        if (! $visit) {
            $visit = $this->triage->registerErVisit([
                'patient_id' => $data['patient_id'],
                'arrived_at' => $data['arrived_at'] ?? now(),
                'arrival_method' => $data['arrival_method'] ?? null,
                'chief_complaint' => $data['chief_complaint'],
                'referral_details' => $data['referral_details'] ?? null,
            ]);
        } else {
            $visit->update([
                'chief_complaint' => $data['chief_complaint'],
                'arrival_method' => $data['arrival_method'] ?? $visit->arrival_method,
                'referral_details' => $data['referral_details'] ?? $visit->referral_details,
                'arrived_at' => $data['arrived_at'] ?? $visit->arrived_at,
            ]);
        }

        if ($assessment) {
            $assessment->update([
                'er_visit_id' => $visit->id,
                'patient_id' => $data['patient_id'],
                'chief_complaint' => $data['chief_complaint'],
                'symptoms' => $this->normalizeSymptoms($data['symptoms'] ?? $assessment->symptoms),
                'pain_score' => $data['pain_score'] ?? $assessment->pain_score,
                'notes' => $data['referral_details'] ?? $assessment->notes,
            ]);

            $assessment->vitals()->updateOrCreate(
                ['triage_assessment_id' => $assessment->id],
                [
                    'patient_id' => $data['patient_id'],
                    'blood_pressure' => $data['blood_pressure'] ?? $assessment->vitals?->blood_pressure,
                    'heart_rate' => $data['heart_rate'] ?? $assessment->vitals?->heart_rate,
                    'respiratory_rate' => $data['respiratory_rate'] ?? $assessment->vitals?->respiratory_rate,
                    'temperature' => $data['temperature'] ?? $assessment->vitals?->temperature,
                    'spo2' => $data['spo2'] ?? $assessment->vitals?->spo2,
                    'recorded_at' => now(),
                ]
            );
        }

        return redirect()->route('emergency.show', $visit)->with('success', 'ER visit registered and linked to the existing triage assessment.');
    }

    protected function normalizeSymptoms($symptoms): array
    {
        if (is_array($symptoms)) {
            return array_values(array_filter(array_map('trim', $symptoms), fn ($item) => $item !== ''));
        }

        return array_values(array_filter(array_map('trim', preg_split('/[,;\n]/', (string) $symptoms ?? '')), fn ($item) => $item !== ''));
    }

    public function show(ErVisit $visit)
    {
        $visit->load(['patient', 'triageAssessments.triageVital', 'queue', 'createdBy']);
        $providers = Provider::where('active', true)->get();

        return view('emergency.show', compact('visit', 'providers'));
    }

    public function triage(Request $request, ErVisit $visit)
    {
        $data = $request->validate([
            'patient_id' => ['nullable', 'exists:patients,id'],
            'chief_complaint' => ['nullable', 'string'],
            'pain_score' => ['nullable', 'integer', 'min:0', 'max:10'],
            'symptoms' => ['nullable', 'string'],
            'priority' => ['nullable', 'in:Level 1,Level 2,Level 3,Level 4,Level 5,Emergency,Urgent,Prompt,Non-Urgent,Routine'],
            'priority_override' => ['nullable', 'in:Emergency,Urgent,Prompt,Non-Urgent,Routine'],
            'ai_confirmed' => ['accepted'],
            'notes' => ['nullable', 'string'],
            'treatment_area' => ['nullable', 'string'],
            'provider_id' => ['nullable', 'exists:providers,id'],
            'vitals_blood_pressure' => ['nullable', 'string'],
            'vitals_heart_rate' => ['nullable', 'integer'],
            'vitals_respiratory_rate' => ['nullable', 'integer'],
            'vitals_temperature' => ['nullable', 'numeric'],
            'vitals_spo2' => ['nullable', 'integer'],
        ]);

        if (! $request->boolean('ai_confirmed')) {
            throw ValidationException::withMessages([
                'ai_confirmed' => ['You must explicitly confirm or override the AI priority before finalizing triage.'],
            ]);
        }

        $priority = $data['priority_override'] ?? $data['priority'] ?? 'Level 3';
        $symptoms = array_values(array_filter(array_map('trim', preg_split('/[,;\n]/', (string) ($data['symptoms'] ?? '')))));

        $assessment = $this->triage->triage($visit, [
            'chief_complaint' => $data['chief_complaint'] ?? null,
            'pain_score' => $data['pain_score'] ?? null,
            'priority' => $this->normalizePriorityForErQueue($priority),
            'notes' => $data['notes'] ?? null,
            'treatment_area' => $data['treatment_area'] ?? null,
            'provider_id' => $data['provider_id'] ?? null,
            'vitals' => [
                'blood_pressure' => $data['vitals_blood_pressure'] ?? null,
                'heart_rate' => $data['vitals_heart_rate'] ?? null,
                'respiratory_rate' => $data['vitals_respiratory_rate'] ?? null,
                'temperature' => $data['vitals_temperature'] ?? null,
                'spo2' => $data['vitals_spo2'] ?? null,
            ],
        ]);

        if ($symptoms !== []) {
            $assessment->update(['symptoms' => $symptoms]);
        }

        return redirect()->route('emergency.show', $visit)->with('success', 'Triage assessment completed.');
    }

    protected function normalizePriorityForErQueue(string $priority): string
    {
        return match (strtolower(trim($priority))) {
            'emergency' => 'Level 1',
            'urgent' => 'Level 2',
            'prompt' => 'Level 3',
            'non-urgent' => 'Level 4',
            'routine' => 'Level 5',
            default => $priority,
        };
    }

    public function queueStatus(Request $request, ErQueue $queue)
    {
        $request->validate([
            'status' => ['required', 'in:WAITING,IN_TREATMENT,DONE'],
            'provider_id' => ['nullable', 'exists:providers,id'],
        ]);

        $this->triage->updateQueueStatus($queue, $request->status, $request->provider_id);

        return back()->with('success', 'ER queue status updated.');
    }
}
