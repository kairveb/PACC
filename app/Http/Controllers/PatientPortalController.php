<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\TelehealthSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientPortalController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $patient = $user?->patient()->with(['appointments.provider.user', 'encounters.provider', 'addresses'])->first();

        if (! $patient) {
            return redirect()->route('patients.profile')->with('warning', 'Complete your registration details to access the patient portal.');
        }

        $upcomingAppointments = $patient->appointments()
            ->with(['provider.user', 'appointmentType'])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        $recentEncounters = $patient->encounters()
            ->with('provider.user')
            ->orderByDesc('started_at')
            ->limit(5)
            ->get();

        $upcomingTelehealth = TelehealthSession::query()
            ->whereHas('appointment', fn ($query) => $query->where('patient_id', $patient->id))
            ->where('status', '!=', TelehealthSession::STATUS_COMPLETED)
            ->with(['appointment.provider.user'])
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        return view('patient-portal.dashboard', compact(
            'patient',
            'upcomingAppointments',
            'recentEncounters',
            'upcomingTelehealth'
        ));
    }

    public function appointments()
    {
        $patient = Auth::user()?->patient()->firstOrFail();

        $appointments = $patient->appointments()
            ->with(['provider.user', 'appointmentType'])
            ->orderByDesc('starts_at')
            ->paginate(10);

        return view('patient-portal.appointments', compact('patient', 'appointments'));
    }

    public function history()
    {
        $patient = Auth::user()?->patient()->firstOrFail();

        $history = $patient->encounters()
            ->with(['provider.user', 'notes'])
            ->orderByDesc('started_at')
            ->paginate(10);

        return view('patient-portal.history', compact('patient', 'history'));
    }

    public function telehealth()
    {
        $patient = Auth::user()?->patient()->firstOrFail();

        $sessions = TelehealthSession::query()
            ->whereHas('appointment', fn ($query) => $query->where('patient_id', $patient->id))
            ->with(['appointment.provider.user'])
            ->orderByDesc('start_time')
            ->paginate(10);

        return view('patient-portal.telehealth', compact('patient', 'sessions'));
    }
}
