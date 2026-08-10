<?php

namespace App\Http\Controllers;

use App\Models\ErQueue;
use App\Models\ErVisit;
use App\Models\Patient;
use App\Models\Provider;
use App\Services\TriageService;
use Illuminate\Http\Request;

class EmergencyController extends Controller
{
    public function __construct(protected TriageService $triage)
    {
    }

    public function index()
    {
        $queue = ErQueue::with(['erVisit.patient', 'provider'])
            ->orderBy('priority')
            ->orderBy('queued_at')
            ->paginate(20);

        $visits = ErVisit::with('patient')->orderBy('arrived_at', 'desc')->limit(10)->get();
        $patients = Patient::orderBy('last_name')->get();

        return view('emergency.index', compact('queue', 'visits', 'patients'));
    }

    public function create()
    {
        $patients = Patient::orderBy('last_name')->get();
        return view('emergency.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'arrived_at' => ['nullable', 'date'],
            'arrival_method' => ['nullable', 'string', 'max:50'],
            'chief_complaint' => ['required', 'string'],
            'referral_details' => ['nullable', 'string'],
        ]);

        $visit = $this->triage->registerErVisit($data);

        return redirect()->route('emergency.show', $visit)->with('success', 'ER visit registered.');
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
            'chief_complaint' => ['nullable', 'string'],
            'pain_score' => ['nullable', 'integer', 'min:0', 'max:10'],
            'priority' => ['required', 'in:Level 1,Level 2,Level 3,Level 4,Level 5'],
            'notes' => ['nullable', 'string'],
            'treatment_area' => ['nullable', 'string'],
            'provider_id' => ['nullable', 'exists:providers,id'],
            'vitals_blood_pressure' => ['nullable', 'string'],
            'vitals_heart_rate' => ['nullable', 'integer'],
            'vitals_respiratory_rate' => ['nullable', 'integer'],
            'vitals_temperature' => ['nullable', 'numeric'],
            'vitals_spo2' => ['nullable', 'integer'],
        ]);

        $assessment = $this->triage->triage($visit, [
            'chief_complaint' => $data['chief_complaint'] ?? null,
            'pain_score' => $data['pain_score'] ?? null,
            'priority' => $data['priority'],
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

        return redirect()->route('emergency.show', $visit)->with('success', 'Triage assessment completed.');
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
