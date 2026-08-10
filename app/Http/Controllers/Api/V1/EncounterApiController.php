<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Encounter;
use App\Services\EncounterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EncounterApiController extends Controller
{
    public function __construct(protected EncounterService $encounterService)
    {
    }

    /**
     * List encounters.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('view-encounters'), 403, 'You are not authorized.');

        $query = Encounter::with('patient', 'provider', 'appointment');

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $encounters = $query->orderByDesc('started_at')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Encounters retrieved successfully.',
            'data' => $encounters->items(),
            'meta' => ['current_page' => $encounters->currentPage(), 'last_page' => $encounters->lastPage(), 'total' => $encounters->total()],
        ]);
    }

    /**
     * Create an encounter.
     */
    public function store(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('create-encounters'), 403, 'You are not authorized.');

        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'provider_id' => ['required', 'exists:providers,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'type' => ['required', 'in:OUTPATIENT,TELEHEALTH,EMERGENCY'],
            'chief_complaint' => ['nullable', 'string'],
            'assessment' => ['nullable', 'string'],
            'plan' => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'date'],
            'vitals' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
        ]);

        $encounter = $this->encounterService->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Encounter created successfully.',
            'data' => $encounter->load('vitals', 'notes'),
        ], 201);
    }

    /**
     * Show an encounter.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $encounter = Encounter::with('patient', 'provider', 'appointment', 'vitals', 'notes')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Encounter retrieved successfully.',
            'data' => $encounter,
        ]);
    }
}
