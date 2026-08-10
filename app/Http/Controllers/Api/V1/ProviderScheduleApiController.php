<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProviderSchedule;
use App\Services\SchedulingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProviderScheduleApiController extends Controller
{
    public function __construct(protected SchedulingService $schedulingService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('view-appointments'), 403, 'You are not authorized.');

        $query = ProviderSchedule::with('provider');

        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->provider_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Provider schedules retrieved successfully.',
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('create-appointments'), 403, 'You are not authorized.');

        $data = $request->validate([
            'provider_id' => ['required', 'exists:providers,id'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_duration' => ['nullable', 'integer', 'min:5', 'max:240'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i'],
            'unavailable_date' => ['nullable', 'date'],
        ]);

        $schedule = ProviderSchedule::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Provider schedule created successfully.',
            'data' => $schedule,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $schedule = ProviderSchedule::with('provider')->findOrFail($id);

        abort_unless(Gate::allows('view-appointments'), 403, 'You are not authorized.');

        return response()->json([
            'success' => true,
            'message' => 'Provider schedule retrieved successfully.',
            'data' => $schedule,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $schedule = ProviderSchedule::findOrFail($id);

        abort_unless(Gate::allows('update-appointments'), 403, 'You are not authorized.');

        $data = $request->validate([
            'day_of_week' => ['sometimes', 'integer', 'between:0,6'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i'],
            'slot_duration' => ['nullable', 'integer', 'min:5', 'max:240'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i'],
            'unavailable_date' => ['nullable', 'date'],
        ]);

        $schedule->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Provider schedule updated successfully.',
            'data' => $schedule,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $schedule = ProviderSchedule::findOrFail($id);

        abort_unless(Gate::allows('delete-appointments'), 403, 'You are not authorized.');

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Provider schedule deleted successfully.',
        ]);
    }

    public function generateSlots(Request $request, int $id): JsonResponse
    {
        $schedule = ProviderSchedule::findOrFail($id);

        abort_unless(Gate::allows('update-appointments'), 403, 'You are not authorized.');

        $data = $request->validate([
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $count = $this->schedulingService->generateSlots($schedule, $data['from_date'], $data['to_date']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment slots generated successfully.',
            'data' => ['generated' => $count],
        ]);
    }
}
