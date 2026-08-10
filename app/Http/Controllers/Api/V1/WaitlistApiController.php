<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Waitlist;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WaitlistApiController extends Controller
{
    public function __construct(protected AppointmentService $appointmentService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('view-appointments'), 403, 'You are not authorized.');

        $query = Waitlist::with(['patient', 'provider', 'department', 'appointmentType']);

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->provider_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $waitlists = $query->orderByDesc('created_at')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Waitlists retrieved successfully.',
            'data' => $waitlists->items(),
            'meta' => [
                'current_page' => $waitlists->currentPage(),
                'last_page' => $waitlists->lastPage(),
                'total' => $waitlists->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('create-appointments'), 403, 'You are not authorized.');

        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'provider_id' => ['nullable', 'exists:providers,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'appointment_type_id' => ['nullable', 'exists:appointment_types,id'],
            'preferred_date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:WAITING,OFFERED,BOOKED,REMOVED'],
        ]);

        $waitlist = $this->appointmentService->addToWaitlist($data);

        return response()->json([
            'success' => true,
            'message' => 'Patient added to waitlist successfully.',
            'data' => $waitlist,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        abort_unless(Gate::allows('update-appointments'), 403, 'You are not authorized.');

        $waitlist = Waitlist::findOrFail($id);

        $data = $request->validate([
            'provider_id' => ['nullable', 'exists:providers,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'appointment_type_id' => ['nullable', 'exists:appointment_types,id'],
            'preferred_date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:WAITING,OFFERED,BOOKED,REMOVED'],
        ]);

        $waitlist->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Waitlist entry updated successfully.',
            'data' => $waitlist,
        ]);
    }
}
