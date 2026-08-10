<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PatientApiController extends Controller
{
    public function __construct(protected PatientService $patientService)
    {
    }

    /**
     * List patients with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('view-patients'), 403, 'You are not authorized to view patients.');

        $patients = $this->patientService->search($request->query('q'), $request->only(['date_of_birth', 'sex']));

        return response()->json([
            'success' => true,
            'message' => 'Patients retrieved successfully.',
            'data' => $patients->items(),
            'meta' => [
                'current_page' => $patients->currentPage(),
                'last_page' => $patients->lastPage(),
                'total' => $patients->total(),
            ],
        ]);
    }

    /**
     * Register a new patient.
     */
    public function store(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('create-patients'), 403, 'You are not authorized to create patients.');

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'sex' => ['required', 'in:Male,Female,Other'],
            'civil_status' => ['nullable', 'string', 'max:30'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'allergies' => ['nullable', 'string'],
            'address' => ['nullable', 'array'],
            'address.line1' => ['nullable', 'string'],
            'address.city' => ['nullable', 'string'],
            'address.province' => ['nullable', 'string'],
            'address.postal_code' => ['nullable', 'string'],
            'emergency_contact' => ['nullable', 'array'],
            'emergency_contact.name' => ['nullable', 'string'],
            'emergency_contact.relationship' => ['nullable', 'string'],
            'emergency_contact.phone' => ['nullable', 'string'],
        ]);

        $patient = $this->patientService->register($data, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Patient registered successfully.',
            'data' => $patient->load('addresses', 'emergencyContacts'),
        ], 201);
    }

    /**
     * Show a single patient.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $patient = Patient::with('addresses', 'emergencyContacts', 'identifiers')->findOrFail($id);

        abort_unless(Gate::allows('view', $patient), 403, 'You are not authorized to view this patient.');

        return response()->json([
            'success' => true,
            'message' => 'Patient retrieved successfully.',
            'data' => $patient,
        ]);
    }

    /**
     * Update a patient.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $patient = Patient::findOrFail($id);

        abort_unless(Gate::allows('update', $patient), 403, 'You are not authorized to update this patient.');

        $data = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['sometimes', 'date', 'before:today'],
            'sex' => ['sometimes', 'in:Male,Female,Other'],
            'civil_status' => ['nullable', 'string', 'max:30'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'allergies' => ['nullable', 'string'],
            'address' => ['nullable', 'array'],
            'emergency_contact' => ['nullable', 'array'],
        ]);

        $patient = $this->patientService->update($patient, $data);

        return response()->json([
            'success' => true,
            'message' => 'Patient updated successfully.',
            'data' => $patient->load('addresses', 'emergencyContacts'),
        ]);
    }

    /**
     * Delete a patient.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $patient = Patient::findOrFail($id);

        abort_unless(Gate::allows('delete', $patient), 403, 'You are not authorized to delete this patient.');

        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Patient deleted successfully.',
        ]);
    }

    /**
     * Search patients.
     */
    public function search(Request $request, string $term): JsonResponse
    {
        abort_unless(Gate::allows('view-patients'), 403, 'You are not authorized to view patients.');

        $patients = $this->patientService->search($term);

        return response()->json([
            'success' => true,
            'message' => 'Patients retrieved successfully.',
            'data' => $patients->items(),
            'meta' => [
                'current_page' => $patients->currentPage(),
                'last_page' => $patients->lastPage(),
                'total' => $patients->total(),
            ],
        ]);
    }
}
