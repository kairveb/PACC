<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Encounter;
use App\Models\EncounterNote;
use App\Services\EncounterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EncounterNoteApiController extends Controller
{
    public function __construct(protected EncounterService $encounterService)
    {
    }

    public function index(Request $request, int $encounterId): JsonResponse
    {
        $encounter = Encounter::findOrFail($encounterId);

        abort_unless(Gate::allows('view', $encounter), 403, 'You are not authorized.');

        $notes = EncounterNote::where('encounter_id', $encounter->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Encounter notes retrieved successfully.',
            'data' => $notes,
        ]);
    }

    public function store(Request $request, int $encounterId): JsonResponse
    {
        $encounter = Encounter::findOrFail($encounterId);

        abort_unless(Gate::allows('update', $encounter), 403, 'You are not authorized.');

        $data = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $note = $this->encounterService->addNote($encounter, $data['body'], $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Encounter note created successfully.',
            'data' => $note,
        ], 201);
    }
}
