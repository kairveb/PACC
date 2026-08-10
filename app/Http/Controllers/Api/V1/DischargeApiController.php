<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Services\AdmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DischargeApiController extends Controller
{
    public function __construct(protected AdmissionService $admissionService)
    {
    }

    /**
     * Discharge a patient.
     */
    public function store(Request $request, int $id): JsonResponse
    {
        abort_unless(Gate::allows('discharge-patients'), 403, 'You are not authorized.');

        $admission = Admission::findOrFail($id);

        $data = $request->validate([
            'reason' => ['nullable', 'string'],
            'disposition' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $discharge = $this->admissionService->discharge($admission, $data);

        return response()->json([
            'success' => true,
            'message' => 'Patient discharged successfully.',
            'data' => $discharge->load('admission'),
        ], 201);
    }
}
