<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PreArrivalProfile;
use App\Rules\PhilippineMobilePhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PreRegistrationController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $patient = $user?->patient()->with(['addresses', 'emergencyContacts'])->first();

        if (! $patient) {
            return redirect()->route('patients.profile')->with('warning', 'Complete your patient profile before pre-registering for care.');
        }

        $address = $patient->primaryAddress();
        $contact = $patient->primaryEmergencyContact();

        return view('portal.pre-register', compact('patient', 'address', 'contact'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $patient = $user?->patient()->firstOrFail();

        $data = $request->validate([
            'visit_reason' => ['required', 'string', 'max:500'],
            'initial_notes' => ['nullable', 'string', 'max:2000'],
            'medical_history' => ['nullable', 'string', 'max:2000'],
            'current_medications' => ['nullable', 'string', 'max:1000'],
            'allergies' => ['nullable', 'string', 'max:1000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30', new PhilippineMobilePhone],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:100'],
            'address_province' => ['nullable', 'string', 'max:100'],
            'address_postal_code' => ['nullable', 'string', 'max:20'],
            'contact_phone' => ['nullable', 'string', 'max:30', new PhilippineMobilePhone],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ]);

        $referenceCode = PreArrivalProfile::generateUniqueReferenceCode();

        $profile = $patient->preArrivalProfiles()->create([
            'token' => (string) Str::uuid(),
            'reference_code' => $referenceCode,
            'status' => 'pending',
            'visit_reason' => $data['visit_reason'],
            'initial_notes' => $data['initial_notes'] ?? null,
            'medical_history' => $data['medical_history'] ?? null,
            'current_medications' => $data['current_medications'] ?? null,
            'allergies' => $data['allergies'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'emergency_contact_relationship' => $data['emergency_contact_relationship'] ?? null,
            'address_line1' => $data['address_line1'] ?? null,
            'address_city' => $data['address_city'] ?? null,
            'address_province' => $data['address_province'] ?? null,
            'address_postal_code' => $data['address_postal_code'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? $patient->phone,
            'contact_email' => $data['contact_email'] ?? $patient->email,
            'qr_code_url' => $this->buildQrCode((string) Str::uuid()),
        ]);

        $profile->update([
            'token' => $profile->token ?: (string) Str::uuid(),
            'qr_code_url' => $this->buildQrCode($profile->token),
        ]);

        return redirect()->route('patients.portal')->with('success', 'Your pre-registration details have been saved. Reference code: ' . $profile->reference_code . '.');
    }

    protected function buildQrCode(string $token): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($token);
    }
}
