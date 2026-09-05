<?php

namespace App\Http\Controllers;

use App\Models\ErVisit;
use App\Models\Patient;
use App\Models\PreArrivalProfile;
use App\Models\TriageAssessment;
use App\Services\AiTriageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TriageAssessmentController extends Controller
{
    public function __construct(protected AiTriageService $aiTriage)
    {
    }

    public function create(): View
    {
        $patients = Patient::orderBy('last_name')->get();

        return view('triage.create', compact('patients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'chief_complaint' => ['required', 'string', 'max:500'],
            'symptoms' => ['nullable', 'string'],
            'pain_score' => ['nullable', 'integer', 'min:0', 'max:10'],
            'blood_pressure' => ['nullable', 'string', 'max:20'],
            'heart_rate' => ['nullable', 'integer', 'min:0', 'max:220'],
            'respiratory_rate' => ['nullable', 'integer', 'min:0', 'max:80'],
            'temperature' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'spo2' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'ai_confirmed' => ['accepted'],
            'priority_override' => ['nullable', 'in:Emergency,Urgent,Prompt,Non-Urgent,Routine'],
        ]);

        if (! $request->boolean('ai_confirmed')) {
            throw ValidationException::withMessages([
                'ai_confirmed' => ['You must explicitly confirm or override the AI priority before finalizing triage.'],
            ]);
        }

        $symptoms = array_values(array_filter(array_map('trim', preg_split('/[,;\n]/', (string) ($data['symptoms'] ?? '')))));
        $preArrival = PreArrivalProfile::where('patient_id', $data['patient_id'])
            ->orderByDesc('created_at')
            ->first();

        $aiInput = [
            'patient_id' => $data['patient_id'],
            'chief_complaint' => $data['chief_complaint'],
            'symptoms' => $symptoms,
            'pain_score' => $data['pain_score'] ?? 0,
            'vitals' => [
                'blood_pressure' => $data['blood_pressure'] ?? null,
                'heart_rate' => $data['heart_rate'] ?? null,
                'respiratory_rate' => $data['respiratory_rate'] ?? null,
                'temperature' => $data['temperature'] ?? null,
                'spo2' => $data['spo2'] ?? null,
            ],
        ];

        if ($preArrival) {
            $aiInput['pre_arrival_profile'] = [
                'visit_reason' => $preArrival->visit_reason,
                'medical_history' => $preArrival->medical_history,
                'current_medications' => $preArrival->current_medications,
                'allergies' => $preArrival->allergies,
                'initial_notes' => $preArrival->initial_notes,
            ];
        }

        $result = $this->aiTriage->analyze($aiInput);

        $finalPriority = $data['priority_override'] ?? $result['priority'];
        $finalNotes = $data['notes'] ?? $result['recommendation'];

        $visit = ErVisit::firstOrCreate(
            [
                'patient_id' => $data['patient_id'],
                'chief_complaint' => $data['chief_complaint'],
            ],
            [
                'visit_number' => app(\App\Services\TriageService::class)->generateVisitNumber(),
                'arrived_at' => now(),
                'arrival_method' => 'Walk-in',
                'referral_details' => $data['notes'] ?? null,
                'status' => ErVisit::STATUS_ARRIVED,
                'created_by' => auth()->id(),
            ]
        );

        $assessment = TriageAssessment::create([
            'patient_id' => $data['patient_id'],
            'er_visit_id' => $visit->id,
            'triage_nurse_id' => auth()->id(),
            'triaged_at' => now(),
            'chief_complaint' => $data['chief_complaint'],
            'symptoms' => $symptoms,
            'pain_score' => $data['pain_score'] ?? null,
            'priority' => $finalPriority,
            'priority_score' => $this->priorityScoreFor($finalPriority),
            'triage_color' => $this->priorityColorFor($finalPriority),
            'notes' => $finalNotes . (empty($data['notes']) ? '' : ' | AI rationale: ' . implode(' ', $result['reasons'])),
            'status' => 'COMPLETE',
        ]);

        $assessment->vitals()->create([
            'patient_id' => $data['patient_id'],
            'blood_pressure' => $data['blood_pressure'] ?? null,
            'heart_rate' => $data['heart_rate'] ?? null,
            'respiratory_rate' => $data['respiratory_rate'] ?? null,
            'temperature' => $data['temperature'] ?? null,
            'spo2' => $data['spo2'] ?? null,
            'recorded_at' => now(),
        ]);

        return redirect()->route('triage.er-intake', $assessment)->with('success', 'AI triage complete. Priority: ' . $finalPriority . ' (' . ucfirst($this->priorityColorFor($finalPriority)) . ')');
    }

    protected function priorityScoreFor(string $priority): int
    {
        return match (strtolower(trim($priority))) {
            'emergency' => 1,
            'urgent' => 2,
            'prompt' => 3,
            'non-urgent' => 4,
            default => 5,
        };
    }

    protected function priorityColorFor(string $priority): string
    {
        return match (strtolower(trim($priority))) {
            'emergency' => 'red',
            'urgent' => 'yellow',
            'prompt' => 'orange',
            default => 'green',
        };
    }
}
