<?php

namespace App\Http\Controllers;

use App\Models\PreArrivalProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ArrivalCheckInController extends Controller
{
    public function lookupByReference(Request $request)
    {
        $user = auth()->user();

        if ($user && $user->hasRole('patient')) {
            abort(403, 'Patients cannot access staff arrival check-in.');
        }

        $referenceCode = strtoupper(trim((string) $request->query('reference_code', '')));

        if ($referenceCode === '') {
            return Redirect::route('emergency.index')->with('error', 'Please enter a valid pre-arrival reference code.');
        }

        $profile = PreArrivalProfile::query()
            ->with('patient')
            ->whereRaw('UPPER(reference_code) = ?', [$referenceCode])
            ->first();

        if (! $profile || ! $profile->isEligibleForCheckIn()) {
            return Redirect::route('emergency.index')->with('error', 'This pre-arrival reference code is invalid, expired, or has already been used.');
        }

        return $this->show($profile->token);
    }

    public function show(string $token)
    {
        $user = auth()->user();

        if ($user && $user->hasRole('patient')) {
            abort(403, 'Patients cannot access staff arrival check-in.');
        }

        $profile = PreArrivalProfile::query()
            ->with('patient')
            ->where('token', $token)
            ->first();

        if (! $profile || ! $profile->isEligibleForCheckIn()) {
            return Redirect::route('emergency.index')->with('error', 'This pre-arrival token is invalid, expired, or has already been used.');
        }

        $patient = $profile->patient;

        $prefill = [
            'patient_id' => $patient?->id,
            'chief_complaint' => $profile->visit_reason,
            'symptoms' => $profile->initial_notes,
            'pain_score' => null,
            'blood_pressure' => null,
            'heart_rate' => null,
            'respiratory_rate' => null,
            'temperature' => null,
            'spo2' => null,
            'referral_details' => null,
            'arrival_method' => 'Walk-in',
            'arrived_at' => now()->format('Y-m-d\TH:i'),
            'checkin_summary' => [
                'patient_name' => $patient?->full_name,
                'age' => $patient?->age,
                'contact_phone' => $profile->contact_phone ?: $patient?->phone,
                'address' => trim(implode(', ', array_filter([
                    $profile->address_line1,
                    $profile->address_city,
                    $profile->address_province,
                    $profile->address_postal_code,
                ]))),
                'medical_history' => $profile->medical_history,
                'allergies' => $profile->allergies,
                'current_medications' => $profile->current_medications,
                'emergency_contact' => trim(implode(' / ', array_filter([
                    $profile->emergency_contact_name,
                    $profile->emergency_contact_phone,
                    $profile->emergency_contact_relationship,
                ]))),
            ],
        ];

        $patients = \App\Models\Patient::orderBy('last_name')->get();

        return view('emergency.create', compact('patients', 'prefill'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user && $user->hasRole('patient')) {
            abort(403, 'Patients cannot access staff arrival check-in.');
        }

        $token = $request->input('token');
        $profile = PreArrivalProfile::query()->where('token', $token)->first();

        if (! $profile || ! $profile->isEligibleForCheckIn()) {
            return Redirect::route('emergency.index')->with('error', 'This pre-arrival token is invalid, expired, or has already been used.');
        }

        $profile->update([
            'status' => 'arrived',
            'arrived_at' => now(),
        ]);

        return Redirect::route('emergency.create')->with('success', 'Patient has been marked as arrived and moved into the intake workflow.');
    }
}
