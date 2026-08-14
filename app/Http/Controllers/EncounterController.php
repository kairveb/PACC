<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Provider;
use App\Services\EncounterService;
use Illuminate\Http\Request;

class EncounterController extends Controller
{
    public function __construct(protected EncounterService $encounters)
    {
    }

    public function index(Request $request)
    {
        $query = Encounter::with(['patient', 'provider'])->orderBy('started_at', 'desc');

        $user = auth()->user();
        if ($user && $user->hasRole('doctor')) {
            $providerId = $user->provider?->id;
            if ($providerId) {
                $query->where('provider_id', $providerId);
            } else {
                $query->whereRaw('0 = 1');
            }
        } elseif ($user && $user->hasRole('nurse')) {
            $query->where('type', Encounter::TYPE_EMERGENCY);
        }

        if ($request->get('type')) {
            $query->where('type', $request->get('type'));
        }
        if ($request->get('q')) {
            $term = $request->get('q');
            $query->whereHas('patient', fn ($p) => $p->where('first_name', 'like', "%{$term}%")->orWhere('last_name', 'like', "%{$term}%"));
        }

        $encounters = $query->paginate(15);

        return view('encounters.index', compact('encounters'));
    }

    public function create(Request $request)
    {
        $patients = Patient::orderBy('last_name')->get();
        $providers = Provider::where('active', true)->get();
        $appointments = Appointment::with('patient')->where('status', Appointment::STATUS_CHECKED_IN)->get();
        $selectedPatient = $request->get('patient_id') ? Patient::find($request->get('patient_id')) : null;

return view('encounters.create', compact('patients', 'providers', 'appointments', 'selectedPatient'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'provider_id' => ['required', 'exists:providers,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'type' => ['required', 'in:OUTPATIENT,TELEHEALTH,EMERGENCY'],
            'chief_complaint' => ['nullable', 'string'],
            'assessment' => ['nullable', 'string'],
            'plan' => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'date'],
            'bp' => ['nullable', 'string'],
            'heart_rate' => ['nullable', 'integer'],
            'respiratory_rate' => ['nullable', 'integer'],
            'temperature' => ['nullable', 'numeric'],
            'spo2' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
        ]);

        $encounter = $this->encounters->create(array_merge($data, [
            'vitals' => [
                'blood_pressure' => $data['bp'] ?? null,
                'heart_rate' => $data['heart_rate'] ?? null,
                'respiratory_rate' => $data['respiratory_rate'] ?? null,
                'temperature' => $data['temperature'] ?? null,
                'spo2' => $data['spo2'] ?? null,
            ],
        ]));

        // Mark appointment as completed if linked
        if ($data['appointment_id'] ?? null) {
            $appointment = Appointment::find($data['appointment_id']);
            if ($appointment && $appointment->status === Appointment::STATUS_CHECKED_IN) {
                $appointment->update(['status' => Appointment::STATUS_COMPLETED]);
            }
        }

        return redirect()->route('encounters.show', $encounter)->with('success', 'Encounter created.');
    }

    public function show(Encounter $encounter)
    {
$encounter->load(['patient', 'provider', 'appointment', 'vitals', 'notes.author']);
        return view('encounters.show', compact('encounter'));
    }

    public function complete(Request $request, Encounter $encounter)
    {
        $data = $request->validate([
            'assessment' => ['nullable', 'string'],
            'plan' => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'date'],
        ]);

        $this->encounters->complete($encounter, $data);

        return back()->with('success', 'Encounter completed.');
    }
}
