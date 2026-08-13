<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\TriageAssessment;
use App\Services\AiTriageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        ]);

        $symptoms = array_values(array_filter(array_map('trim', preg_split('/[,;\n]/', (string) ($data['symptoms'] ?? '')))));
        $result = $this->aiTriage->analyze([
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
        ]);

        $assessment = TriageAssessment::create([
            'patient_id' => $data['patient_id'],
            'triage_nurse_id' => auth()->id(),
            'triaged_at' => now(),
            'chief_complaint' => $data['chief_complaint'],
            'symptoms' => $symptoms,
            'pain_score' => $data['pain_score'] ?? null,
            'priority' => $result['priority'],
            'priority_score' => $result['level'],
            'triage_color' => $result['color'],
            'notes' => $data['notes'] ?? $result['recommendation'],
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

        return redirect()->route('triage.er-intake', $assessment)->with('success', 'AI triage complete. Priority: ' . $result['priority'] . ' (' . ucfirst($result['color']) . ')');
    }
}
