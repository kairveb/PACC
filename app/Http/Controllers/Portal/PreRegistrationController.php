<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PreArrivalProfile;
use App\Rules\PhilippineMobilePhone;
use App\Services\PatientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PreRegistrationController extends Controller
{
    public function __construct(protected PatientService $patientService)
    {
    }

    public function publicCreate()
    {
        return view('portal.public-pre-register');
    }

    public function publicStore(Request $request)
    {
        $data = $this->validatePreRegistration($request, true);

        $patient = $this->patientService->register([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'] ?? null,
            'date_of_birth' => $data['date_of_birth'],
            'sex' => $data['sex'],
            'civil_status' => $data['civil_status'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'allergies' => $data['allergies'] ?? null,
            'verified' => false,
            'address' => [
                'line1' => $data['address_line1'] ?? null,
                'barangay' => $data['address_barangay'] ?? null,
                'city' => $data['address_city'] ?? null,
                'province' => $data['address_province'] ?? null,
                'postal_code' => $data['address_postal'] ?? null,
            ],
            'emergency_contact' => [
                'name' => $data['emergency_name'] ?? null,
                'relationship' => $data['emergency_relationship'] ?? null,
                'phone' => $data['emergency_phone'] ?? null,
            ],
        ]);

        $profile = $this->createProfile($patient, $data);

        return view('portal.public-pre-register-confirmation', compact('profile'));
    }

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

        $data = $this->validatePreRegistration($request);

        $preferredFirstName = $data['first_name'] ?? $patient->first_name;

        $preferredMiddleName = $data['middle_name'] ?? $patient->middle_name;
        $preferredLastName = $data['last_name'] ?? $patient->last_name;
        $preferredSuffix = $data['suffix'] ?? $patient->suffix;
        $preferredDateOfBirth = $data['date_of_birth'] ?? $patient->date_of_birth?->toDateString();
        $preferredSex = $data['sex'] ?? $patient->sex;
        $preferredCivilStatus = $data['civil_status'] ?? $patient->civil_status;
        $preferredNationality = $data['nationality'] ?? $patient->nationality;
        $preferredPhone = $data['phone'] ?? $patient->phone;
        $preferredEmail = $data['email'] ?? $patient->email;
        $preferredEmergencyName = $data['emergency_name'] ?? null;
        $preferredEmergencyPhone = $data['emergency_phone'] ?? null;
        $preferredEmergencyRelationship = $data['emergency_relationship'] ?? null;
        $preferredPostal = $data['address_postal'] ?? null;

        $referenceCode = PreArrivalProfile::generateUniqueReferenceCode();
        $token = (string) Str::uuid();

        $profileData = [
            'token' => $token,
            'reference_code' => $referenceCode,
            'status' => 'pending',
            'first_name' => $preferredFirstName,
            'middle_name' => $preferredMiddleName,
            'last_name' => $preferredLastName,
            'suffix' => $preferredSuffix,
            'date_of_birth' => $preferredDateOfBirth,
            'sex' => $preferredSex,
            'civil_status' => $preferredCivilStatus,
            'nationality' => $preferredNationality,
            'phone' => $preferredPhone,
            'email' => $preferredEmail,
            'visit_reason' => $data['visit_reason'] ?? null,
            'initial_notes' => $data['initial_notes'] ?? null,
            'medical_history' => $data['medical_history'] ?? null,
            'current_medications' => $data['current_medications'] ?? null,
            'allergies' => $data['allergies'] ?? null,
            'emergency_name' => $preferredEmergencyName,
            'emergency_phone' => $preferredEmergencyPhone,
            'emergency_relationship' => $preferredEmergencyRelationship,
            'address_line1' => $data['address_line1'] ?? null,
            'address_barangay' => $data['address_barangay'] ?? null,
            'address_city' => $data['address_city'] ?? null,
            'address_province' => $data['address_province'] ?? null,
            'address_postal' => $preferredPostal,
        ];

        $profile = $patient->preArrivalProfiles()->create($profileData);

        $profile->update([
            'token' => $profile->token ?: $token,
            'qr_code_url' => $this->buildQrCode($profile->token),
        ]);

        return redirect()->route('patients.portal')->with('success', 'Your pre-registration details have been saved. Reference code: ' . $profile->reference_code . '.');
    }

    protected function validatePreRegistration(Request $request, bool $newPatient = false): array
    {
        $presence = $newPatient ? 'required' : 'nullable';

        return $request->validate([
            'first_name' => [$presence, 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => [$presence, 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => [$presence, 'date', 'before_or_equal:today'],
            'sex' => [$presence, 'in:Male,Female,Other'],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30', new PhilippineMobilePhone],
            'email' => ['nullable', 'email', 'max:255'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_barangay' => ['nullable', 'string', 'max:150'],
            'address_city' => ['nullable', 'string', 'max:100'],
            'address_province' => ['nullable', 'string', 'max:100'],
            'address_postal' => ['nullable', 'string', 'max:10'],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'emergency_name' => ['nullable', 'string', 'max:150'],
            'emergency_phone' => ['nullable', 'string', 'max:30', new PhilippineMobilePhone],
            'emergency_relationship' => ['nullable', 'string', 'max:50'],
            'visit_reason' => ['nullable', 'string', 'max:500'],
            'initial_notes' => ['nullable', 'string', 'max:2000'],
            'medical_history' => ['nullable', 'string', 'max:2000'],
            'current_medications' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    protected function createProfile(Patient $patient, array $data): PreArrivalProfile
    {
        $profile = $patient->preArrivalProfiles()->create([
            'token' => (string) Str::uuid(),
            'reference_code' => PreArrivalProfile::generateUniqueReferenceCode(),
            'status' => 'pending',
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'] ?? null,
            'date_of_birth' => $data['date_of_birth'],
            'sex' => $data['sex'],
            'civil_status' => $data['civil_status'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'visit_reason' => $data['visit_reason'] ?? null,
            'initial_notes' => $data['initial_notes'] ?? null,
            'medical_history' => $data['medical_history'] ?? null,
            'current_medications' => $data['current_medications'] ?? null,
            'allergies' => $data['allergies'] ?? null,
            'emergency_name' => $data['emergency_name'] ?? null,
            'emergency_phone' => $data['emergency_phone'] ?? null,
            'emergency_relationship' => $data['emergency_relationship'] ?? null,
            'address_line1' => $data['address_line1'] ?? null,
            'address_barangay' => $data['address_barangay'] ?? null,
            'address_city' => $data['address_city'] ?? null,
            'address_province' => $data['address_province'] ?? null,
            'address_postal' => $data['address_postal'] ?? null,
        ]);

        $profile->update(['qr_code_url' => $this->buildQrCode($profile->token)]);

        return $profile;
    }

    protected function buildQrCode(string $token): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($token);
    }
}
