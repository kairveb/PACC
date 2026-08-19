<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TelehealthSession;
use App\Services\TelehealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TelehealthParticipantApiController extends Controller
{
    public function __construct(protected TelehealthService $telehealthService)
    {
    }

    public function store(Request $request, int $sessionId): JsonResponse
    {
        $session = TelehealthSession::findOrFail($sessionId);

        if ($request->user()?->hasRole('patient')) {
            $patientId = $request->user()->patient?->id;
            $sessionPatientId = $session->appointment?->patient_id;

            if ($patientId === null || $sessionPatientId !== $patientId) {
                abort(403, 'You can only add participants to your own telehealth session.');
            }
        }

        abort_unless(Gate::allows('join-telehealth'), 403, 'You are not authorized.');

        $data = $request->validate([
            'participant_type' => ['required', 'in:provider,patient,guardian,caregiver'],
            'participant_id' => ['required', 'integer'],
        ]);

        $participant = $this->telehealthService->addParticipant(
            $session,
            $data['participant_id'],
            $data['participant_type']
        );

        return response()->json([
            'success' => true,
            'message' => 'Telehealth participant added successfully.',
            'data' => $participant,
        ], 201);
    }

    public function index(Request $request, int $sessionId): JsonResponse
    {
        $session = TelehealthSession::findOrFail($sessionId);

        if ($request->user()?->hasRole('patient')) {
            $patientId = $request->user()->patient?->id;
            $sessionPatientId = $session->appointment?->patient_id;

            if ($patientId === null || $sessionPatientId !== $patientId) {
                abort(403, 'You can only view participants for your own telehealth session.');
            }
        }

        abort_unless(Gate::allows('view', $session), 403, 'You are not authorized.');

        $participants = $session->participants()->get();

        return response()->json([
            'success' => true,
            'message' => 'Telehealth participants retrieved successfully.',
            'data' => $participants,
        ]);
    }
}
