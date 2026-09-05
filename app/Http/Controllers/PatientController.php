<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Rules\PhilippineMobilePhone;
use App\Services\AuditLogService;
use App\Services\PatientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    public function __construct(
        protected PatientService $patientService,
        protected AuditLogService $audit
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Patient::class);

        $query = Patient::query()
            ->with(['addresses', 'emergencyContacts'])
            ->orderBy('created_at', 'desc');

        $user = auth()->user();
        if ($user && $user->hasRole('doctor')) {
            $providerId = $user->provider?->id;
            if ($providerId) {
                $query->where(function ($q) use ($providerId) {
                    $q->whereHas('encounters', fn ($encounterQuery) => $encounterQuery->where('provider_id', $providerId))
                        ->orWhereHas('appointments', fn ($appointmentQuery) => $appointmentQuery->where('provider_id', $providerId));
                });
            } else {
                $query->whereRaw('0 = 1');
            }
        } elseif ($user && $user->hasRole('nurse')) {
            $query->where(function ($q) {
                $q->whereHas('erVisits')
                    ->orWhereHas('triageAssessments');
            });
        }

        if ($request->filled('q')) {
            $term = $request->get('q');
            $like = '%'.$term.'%';
            $query->where(function ($q) use ($like, $term) {
                $q->where('mrn', 'like', $like)
                    ->orWhere('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('middle_name', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }

        if ($request->filled('date_of_birth')) {
            $query->where('date_of_birth', $request->get('date_of_birth'));
        }

        if ($request->filled('sex')) {
            $query->where('sex', $request->get('sex'));
        }

        $patients = $query->paginate(15);

        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        return redirect()->route('patients.index');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Patient::class);

$data = $request->validate([
            // Text fields (names, places) — letters & spaces only, trimmed.
            'first_name' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\'\-.]+$/u'],
            'middle_name' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\s\'\-.]+$/u'],
            'last_name' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\'\-.]+$/u'],
            'suffix' => ['nullable', 'string', 'max:20', 'regex:/^[\pL\s\'\-.]+$/u'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'sex' => ['required', 'in:Male,Female,Other'],
            'civil_status' => ['nullable', 'string', 'max:50', 'regex:/^[\pL\s\'\-.]+$/u'],
            'nationality' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\s\'\-.]+$/u'],
            // Philippine mobile numbers only.
            'phone' => ['nullable', 'string', 'max:30', new PhilippineMobilePhone],
            // Email — valid format, lowercase.
            'email' => ['nullable', 'email', 'lowercase', 'max:255'],
            'allergies' => ['nullable', 'string', 'max:2000'],
            // Places (address) — letters, digits, comma, period, dash.
            'address_line1' => ['nullable', 'string', 'max:255', 'regex:/^[\pL\pN\s\'\-\.,#]+$/u'],
            'address_barangay' => ['nullable', 'string', 'max:150', 'regex:/^[\pL\pN\s\'\-.]+$/u'],
            'address_city' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\pN\s\'\-.]+$/u'],
            'address_province' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\pN\s\'\-.]+$/u'],
            'address_postal' => ['nullable', 'string', 'max:10', 'regex:/^[0-9]{4}$/'],
            // Emergency contact (reasons/names/numbers).
            'emergency_name' => ['nullable', 'string', 'max:150', 'regex:/^[\pL\s\'\-.]+$/u'],
            'emergency_relationship' => ['nullable', 'string', 'max:50', 'regex:/^[\pL\s\'\-.]+$/u'],
            'emergency_phone' => ['nullable', 'string', 'max:30', new PhilippineMobilePhone],
        ], [
            'first_name.regex' => 'First name may only contain letters, spaces, and basic punctuation.',
            'last_name.regex' => 'Last name may only contain letters, spaces, and basic punctuation.',
            'phone' => 'Contact number must be a valid Philippine mobile number (09XXXXXXXXX or +639XXXXXXXXX).',
            'emergency_phone' => 'Emergency contact number must be a valid Philippine mobile number (09XXXXXXXXX or +639XXXXXXXXX).',
            'address_postal.regex' => 'Postal code must be exactly 4 digits.',
            'date_of_birth.before_or_equal' => 'Date of birth cannot be in the future.',
        ]);

        // Duplicate detection
        $duplicates = $this->patientService->findDuplicates([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'date_of_birth' => $data['date_of_birth'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
        ]);

        if ($request->has('force') && $request->boolean('force')) {
            $patient = $this->createPatient($data);
            return redirect()->route('patients.show', $patient)->with('success', 'Patient registered successfully.');
        }

        if (count($duplicates) > 0) {
            return back()->withInput()->with([
                'duplicate_warning' => 'Possible existing patient record found. Please verify before proceeding.',
                'duplicates' => $duplicates,
            ]);
        }

        $patient = $this->createPatient($data);

        return redirect()->route('patients.show', $patient)->with('success', 'Patient registered successfully.');
    }

    protected function createPatient(array $data): Patient
    {
        return $this->patientService->register([
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
        ], auth()->id());
    }

    public function show(Patient $patient)
    {
        $this->authorize('view', $patient);

        $patient->load([
            'addresses',
            'emergencyContacts',
            'identifiers',
            'consents',
            'appointments.provider',
            'encounters.provider',
'erVisits.triageAssessments',
            'admissions.bedAssignments.bed.room.ward',
            'admissions.transfers',
            'admissions.discharge',
            'clinicalDocuments',
        ]);

        $this->audit->viewPatient($patient->id);

return view('patients.show', compact('patient'));
    }

    public function showVitals(Patient $patient)
    {
        $this->authorize('view', $patient);
        $vitals = $patient->vitals()->with('encounter')->orderBy('recorded_at', 'desc')->paginate(20);
        return view('patients.partials.vitals', compact('patient', 'vitals'));
    }

    public function search(Request $request)
    {
        $term = $request->get('q');
        $patients = $this->patientService->search($term);

        return response()->json([
            'data' => $patients->items(),
            'meta' => [
                'current_page' => $patients->currentPage(),
                'last_page' => $patients->lastPage(),
                'total' => $patients->total(),
            ],
        ]);
    }

    public function profile()
    {
        $user = Auth::user();

        $patient = $user->patient()->with(['addresses', 'emergencyContacts'])->first();

        if (! $patient) {
            $patient = $user->patient()->create([
                'mrn' => $this->patientService->generateMpn(),
                'first_name' => $user->name,
                'last_name' => '',
                'date_of_birth' => now()->subYears(18)->toDateString(),
                'sex' => 'Other',
                'email' => $user->email,
                'lookup_code' => strtoupper(Str::random(8)),
                'pre_registration_status' => 'not_started',
            ]);
        }

        return view('patients.profile', compact('patient'));
    }

    public function saveProfile(Request $request)
    {
        $user = $request->user();

        $patient = $user->patient()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'mrn' => $this->patientService->generateMpn(),
                'first_name' => $user->name,
                'last_name' => '',
                'email' => $user->email,
                'lookup_code' => strtoupper(Str::random(8)),
                'pre_registration_status' => 'not_started',
            ]
        );

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'sex' => ['required', 'in:Male,Female,Other'],
            'phone' => ['nullable', 'string', 'max:30', new PhilippineMobilePhone],
            'email' => ['nullable', 'email', 'max:255'],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'address' => ['nullable', 'array'],
            'address.line1' => ['nullable', 'string'],
            'address.barangay' => ['nullable', 'string'],
            'address.city' => ['nullable', 'string'],
            'address.province' => ['nullable', 'string'],
            'address.postal_code' => ['nullable', 'string'],
            'emergency_contact' => ['nullable', 'array'],
            'emergency_contact.name' => ['nullable', 'string'],
            'emergency_contact.relationship' => ['nullable', 'string'],
            'emergency_contact.phone' => ['nullable', 'string', new PhilippineMobilePhone],
        ]);

        $this->patientService->update($patient, $data);
        $patient->markPendingArrival();

        return redirect()->route('dashboard')->with('success', 'Your pre-registration details have been saved. Your visit reference is '.$patient->lookup_code.'.');
    }

    public function lookup(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $lookupCode = trim((string) $request->query('lookup_code', ''));

        $query = Patient::query()
            ->with(['addresses', 'emergencyContacts'])
            ->where(function ($q) use ($term, $lookupCode) {
                if ($lookupCode !== '') {
                    $q->orWhere('lookup_code', strtoupper($lookupCode));
                }

                if ($term !== '') {
                    $search = '%'.$term.'%';
                    $q->orWhere('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('lookup_code', 'like', $search);
                }
            });

        $patients = $query->limit(10)->get();

        return response()->json([
            'data' => $patients->map(function ($patient) {
                $address = $patient->addresses->first();
                $contact = $patient->emergencyContacts->first();

                return [
                    'id' => $patient->id,
                    'mrn' => $patient->mrn,
                    'lookup_code' => $patient->lookup_code,
                    'first_name' => $patient->first_name,
                    'middle_name' => $patient->middle_name,
                    'last_name' => $patient->last_name,
                    'date_of_birth' => $patient->date_of_birth?->format('Y-m-d'),
                    'sex' => $patient->sex,
                    'phone' => $patient->phone,
                    'email' => $patient->email,
                    'address' => $address ? [
                        'line1' => $address->line1,
                        'city' => $address->city,
                        'province' => $address->province,
                        'postal_code' => $address->postal_code,
                    ] : null,
                    'emergency_contact' => $contact ? [
                        'name' => $contact->name,
                        'relationship' => $contact->relationship,
                        'phone' => $contact->phone,
                    ] : null,
                ];
            }),
        ]);
    }

    public function verify(Patient $patient)
    {
        $this->authorize('update', $patient);
        $this->patientService->verify($patient, auth()->id());
        return back()->with('success', 'Patient verified successfully.');
    }
}
