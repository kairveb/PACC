<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProviderSchedule;
use App\Services\SchedulingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ScheduleApiController extends Controller
{
    public function __construct(protected SchedulingService $schedulingService)
    {
    }

    /**
     * List provider schedules.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('view-appointments'), 403, 'You are not authorized.');

        $query = ProviderSchedule::with('provider');

        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->provider_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Schedules retrieved successfully.',
            'data' => $query->get(),
        ]);
    }

    /**
     * Get available slots for a provider on a given date.
     */
    public function slots(Request $request, int $providerId): JsonResponse
    {
        abort_unless(Gate::allows('view-appointments'), 403, 'You are not authorized.');

        $data = $request->validate([
            'date' => ['required', 'date'],
            'appointment_type_id' => ['nullable', 'exists:appointment_types,id'],
        ]);

        $slots = $this->schedulingService->availableSlots($providerId, $data['date'], $data['appointment_type_id'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Available slots retrieved successfully.',
            'data' => $slots,
        ]);
    }
}
