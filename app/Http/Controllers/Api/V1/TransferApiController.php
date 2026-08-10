<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Services\BedManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TransferApiController extends Controller
{
    public function __construct(protected BedManagementService $bedManagement)
    {
    }

    /**
     * Transfer a patient to a new bed.
     */
    public function store(Request $request, int $id): JsonResponse
    {
        abort_unless(Gate::allows('transfer-patients'), 403, 'You are not authorized.');

        $admission = Admission::findOrFail($id);

        $data = $request->validate([
            'to_bed_id' => ['required', 'exists:beds,id'],
            'reason' => ['nullable', 'string'],
        ]);

        $transfer = $this->bedManagement->transfer($admission, $data['to_bed_id'], $data['reason'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Patient transferred successfully.',
            'data' => $transfer->load('fromBed', 'toBed', 'admission'),
        ], 201);
    }
}
