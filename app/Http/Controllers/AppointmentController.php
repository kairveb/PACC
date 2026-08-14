<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Provider;
use App\Services\AppointmentService;
use App\Services\SchedulingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        protected AppointmentService $appointments,
        protected SchedulingService $scheduling
    ) {
    }

    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'provider', 'department', 'appointmentType'])
            ->orderBy('starts_at', 'desc');

        $user = auth()->user();
        if ($user && $user->hasRole('doctor')) {
            $providerId = $user->provider?->id;
            if ($providerId) {
                $query->where('provider_id', $providerId);
            } else {
                $query->whereRaw('0 = 1');
            }
        } elseif ($user && $user->hasRole('patient')) {
            $patientId = $user->patient?->id;
            if ($patientId) {
                $query->where('patient_id', $patientId);
            } else {
                $query->whereRaw('0 = 1');
            }
        } elseif ($user && $user->hasRole('nurse')) {
            $query->whereIn('status', [Appointment::STATUS_CHECKED_IN, Appointment::STATUS_IN_CONSULTATION, Appointment::STATUS_CONFIRMED])
                ->whereDate('starts_at', today());
        }

        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->get('date')) {
            $query->whereDate('starts_at', $request->get('date'));
        }
        if ($request->filled('follow_up')) {
            if ($request->get('follow_up') === 'due') {
                $query->whereHas('encounter', function ($encounterQuery) {
                    $encounterQuery->whereNotNull('follow_up_date')
                        ->whereDate('follow_up_date', '<=', now()->addDays(7)->toDateString());
                });
            }
        }
        if ($request->get('q')) {
            $term = $request->get('q');
            $query->where(function ($q) use ($term) {
                $q->where('appointment_number', 'like', "%{$term}%")
                    ->orWhereHas('patient', fn ($p) => $p->where('first_name', 'like', "%{$term}%")->orWhere('last_name', 'like', "%{$term}%"));
            });
        }

        $appointments = $query->paginate(15);

        return view('appointments.index', [
            'appointments' => $appointments,
            'statuses' => [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_IN_CONSULTATION,
                Appointment::STATUS_COMPLETED,
                Appointment::STATUS_CANCELLED,
                Appointment::STATUS_NO_SHOW,
            ],
        ]);
    }

    public function create()
    {
        $patients = Patient::orderBy('last_name')->get();
        $providers = Provider::where('active', true)->with('department')->get();
        $departments = Department::orderBy('name')->get();
        $appointmentTypes = AppointmentType::orderBy('name')->get();

        return view('appointments.create', compact('patients', 'providers', 'departments', 'appointmentTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'provider_id' => ['required', 'exists:providers,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'appointment_type_id' => ['nullable', 'exists:appointment_types,id'],
            'starts_at' => ['required', 'date'],
            'duration' => ['nullable', 'integer', 'min:5', 'max:240'],
            'reason' => ['nullable', 'string'],
        ]);

        try {
            $appointment = $this->appointments->book($data, auth()->id());
            return redirect()->route('appointments.show', $appointment)
                ->with('success', 'Appointment booked successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'provider', 'department', 'appointmentType', 'statusHistories.user', 'encounter', 'telehealthSession']);
        return view('appointments.show', compact('appointment'));
    }

    public function slots(Request $request)
    {
        $request->validate([
            'provider_id' => ['required', 'exists:providers,id'],
            'date' => ['required', 'date'],
        ]);

        $slots = $this->scheduling->availableSlots($request->provider_id, $request->date);

        return response()->json(['data' => $slots]);
    }

    public function checkIn(Appointment $appointment)
    {
        try {
            $this->appointments->checkIn($appointment, auth()->id());
            return back()->with('success', 'Patient checked in successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        $data = $request->validate(['reason' => ['nullable', 'string']]);
        try {
            $this->appointments->cancel($appointment, auth()->id(), $data['reason'] ?? null);
            return back()->with('success', 'Appointment cancelled.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'starts_at' => ['required', 'date'],
            'duration' => ['nullable', 'integer'],
        ]);

        try {
            $this->appointments->reschedule($appointment, $data, auth()->id());
            return back()->with('success', 'Appointment rescheduled.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }
    }

    public function markNoShow(Appointment $appointment)
    {
        try {
            $this->appointments->markNoShow($appointment, auth()->id());
            return back()->with('success', 'Marked as no-show.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}
