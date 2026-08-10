<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Services\AdmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdmissionApiController extends Controller
{
    public function __construct(protected AdmissionService $admissionService)
    {
    }

    /**
     * List admissions.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('view-admissions'), 403, 'You are not authorized.');

        $query = Admission::with('patient', 'attendingProvider', 'activeBedAssignment.bed');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $admissions = $query->orderByDesc('created_at')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Admissions retrieved successfully.',
            'data' => $admissions->items(),
            'meta' => ['current_page' => $admissions->currentPage(), 'last_page' => $admissions->lastPage(), 'total' => $admissions->total()],
        ]);
    }

    /**
     * Create an admission request.
     */
    public function store(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('create-admissions'), 403, 'You are not authorized.');

        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'er_visit_id' => ['nullable', 'exists:er_visits,id'],
            'attending_provider_id' => ['nullable', 'exists:providers,id'],
            'reason' => ['nullable', 'string'],
        ]);

        $admission = $this->admissionService->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Admission request created successfully.',
            'data' => $admission->load('patient'),
        ], 201);
    }

    /**
     * Show an admission.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $admission = Admission::with('patient', 'attendingProvider', 'erVisit', 'bedAssignments.bed', 'bedReservations', 'transfers', 'discharge')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Admission retrieved successfully.',
            'data' => $admission,
        ]);
    }
}
