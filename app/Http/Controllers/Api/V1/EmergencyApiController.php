<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ErQueue;
use App\Models\ErVisit;
use App\Services\TriageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EmergencyApiController extends Controller
{
    public function __construct(protected TriageService $triageService)
    {
    }

    /**
     * Get the ER queue.
     */
    public function queue(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('view-er'), 403, 'You are not authorized.');

        $query = ErQueue::with('erVisit.patient')
            ->orderByRaw("CASE priority
                WHEN 'Level 1' THEN 1
                WHEN 'Level 2' THEN 2
                WHEN 'Level 3' THEN 3
                WHEN 'Level 4' THEN 4
                WHEN 'Level 5' THEN 5
                ELSE 99
            END")
            ->orderBy('queued_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $queue = $query->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'ER queue retrieved successfully.',
            'data' => $queue->items(),
            'meta' => ['current_page' => $queue->currentPage(), 'last_page' => $queue->lastPage(), 'total' => $queue->total()],
        ]);
    }

    /**
     * Register an ER visit (arrival).
     */
    public function storeVisit(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('create-er-visits'), 403, 'You are not authorized.');

        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'arrived_at' => ['nullable', 'date'],
            'arrival_method' => ['nullable', 'string', 'max:100'],
            'chief_complaint' => ['required', 'string'],
            'referral_details' => ['nullable', 'string'],
        ]);

        $visit = $this->triageService->registerErVisit($data);

        return response()->json([
            'success' => true,
            'message' => 'ER visit registered successfully.',
            'data' => $visit->load('patient'),
        ], 201);
    }

    /**
     * Show an ER visit.
     */
    public function showVisit(Request $request, int $id): JsonResponse
    {
        $visit = ErVisit::with('patient', 'triage', 'queue')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'ER visit retrieved successfully.',
            'data' => $visit,
        ]);
    }
}
