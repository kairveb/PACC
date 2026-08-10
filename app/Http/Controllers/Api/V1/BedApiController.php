<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Bed;
use App\Services\BedManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BedApiController extends Controller
{
    public function __construct(protected BedManagementService $bedManagement)
    {
    }

    /**
     * List beds with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('view-beds'), 403, 'You are not authorized.');

        $query = Bed::with('room.ward', 'activeAssignment', 'activeReservation');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('ward_id')) {
            $query->whereHas('room', fn ($q) => $q->where('ward_id', $request->ward_id));
        }

        $beds = $query->orderBy('room_id')->orderBy('number')->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Beds retrieved successfully.',
            'data' => $beds->items(),
            'meta' => ['current_page' => $beds->currentPage(), 'last_page' => $beds->lastPage(), 'total' => $beds->total()],
        ]);
    }

    /**
     * List available beds.
     */
    public function available(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('view-beds'), 403, 'You are not authorized.');

        $beds = $this->bedManagement->availableBeds($request->integer('ward_id'));

        return response()->json([
            'success' => true,
            'message' => 'Available beds retrieved successfully.',
            'data' => $beds,
        ]);
    }

    /**
     * Show a single bed.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $bed = Bed::with('room.ward', 'activeAssignment', 'activeReservation', 'assignments')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Bed retrieved successfully.',
            'data' => $bed,
        ]);
    }

    /**
     * Reserve a bed for an admission.
     */
    public function reserve(Request $request, int $id): JsonResponse
    {
        abort_unless(Gate::allows('manage-beds'), 403, 'You are not authorized.');

        $data = $request->validate([
            'admission_id' => ['required', 'exists:admissions,id'],
            'expires_in_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
        ]);

        $admission = Admission::findOrFail($data['admission_id']);

        $reservation = $this->bedManagement->reserveBed($admission, $id, $data['expires_in_minutes'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Bed reserved successfully.',
            'data' => $reservation->load('bed'),
        ], 201);
    }

    /**
     * Assign a bed to an admission.
     */
    public function assign(Request $request, int $id): JsonResponse
    {
        abort_unless(Gate::allows('manage-beds'), 403, 'You are not authorized.');

        $data = $request->validate([
            'admission_id' => ['required', 'exists:admissions,id'],
            'reservation_id' => ['nullable', 'exists:bed_reservations,id'],
        ]);

        $admission = Admission::findOrFail($data['admission_id']);

        $assignment = $this->bedManagement->assignBed($admission, $id, $data['reservation_id'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Bed assigned successfully.',
            'data' => $assignment->load('bed', 'admission'),
        ], 201);
    }

    /**
     * Release a bed.
     */
    public function release(Request $request, int $id): JsonResponse
    {
        abort_unless(Gate::allows('manage-beds'), 403, 'You are not authorized.');

        $data = $request->validate([
            'admission_id' => ['required', 'exists:admissions,id'],
        ]);

        $admission = Admission::findOrFail($data['admission_id']);

        $bed = $this->bedManagement->releaseBed($admission, $id);

        return response()->json([
            'success' => true,
            'message' => 'Bed released successfully.',
            'data' => $bed,
        ]);
    }
}

