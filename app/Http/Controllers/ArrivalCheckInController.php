<?php

namespace App\Http\Controllers;

use App\Models\PreArrivalProfile;
use App\Services\PatientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class ArrivalCheckInController extends Controller
{
    public function __construct(protected PatientService $patientService)
    {
    }

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

        return view('emergency.checkin-review', [
            'profile' => $profile,
            'patient' => $profile->patient,
        ]);
    }

    public function confirm(Request $request)
    {
        $user = auth()->user();

        if ($user && $user->hasRole('patient')) {
            abort(403, 'Patients cannot access staff arrival check-in.');
        }

        $profile = PreArrivalProfile::query()->with('patient')->where('token', $request->input('token'))->first();

        if (! $profile || ! $profile->isEligibleForCheckIn()) {
            return Redirect::route('emergency.index')->with('error', 'This pre-arrival token is invalid, expired, or has already been used.');
        }

        $patient = $profile->patient;

        DB::transaction(function () use ($profile, $patient) {
            $this->patientService->update($patient, [
                'first_name' => $profile->first_name ?? $patient->first_name,
                'middle_name' => $profile->middle_name ?? $patient->middle_name,
                'last_name' => $profile->last_name ?? $patient->last_name,
                'suffix' => $profile->suffix ?? $patient->suffix,
                'date_of_birth' => $profile->date_of_birth ?? $patient->date_of_birth,
                'sex' => $profile->sex ?? $patient->sex,
                'civil_status' => $profile->civil_status ?? $patient->civil_status,
                'nationality' => $profile->nationality ?? $patient->nationality,
                'phone' => $profile->phone ?? $profile->contact_phone ?? $patient->phone,
                'email' => $profile->email ?? $profile->contact_email ?? $patient->email,
                'allergies' => $profile->allergies ?? $patient->allergies,
                'address' => [
                    'line1' => $profile->address_line1,
                    'barangay' => $profile->address_barangay,
                    'city' => $profile->address_city,
                    'province' => $profile->address_province,
                    'postal_code' => $profile->address_postal ?? $profile->address_postal_code,
                ],
                'emergency_contact' => [
                    'name' => $profile->emergency_name ?? $profile->emergency_contact_name,
                    'relationship' => $profile->emergency_relationship ?? $profile->emergency_contact_relationship,
                    'phone' => $profile->emergency_phone ?? $profile->emergency_contact_phone,
                ],
            ]);

            $profile->update([
                'status' => 'arrived',
                'arrived_at' => now(),
            ]);
        });

        return Redirect::route('patients.show', $patient)->with('success', 'Patient registration was confirmed and the patient has been marked as arrived.');
    }
}
