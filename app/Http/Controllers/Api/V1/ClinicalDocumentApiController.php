<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ClinicalDocument;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ClinicalDocumentApiController extends Controller
{
    public function index(Request $request, int $patientId): JsonResponse
    {
        $patient = Patient::findOrFail($patientId);

        abort_unless(Gate::allows('view', $patient), 403, 'You are not authorized.');

        $documents = ClinicalDocument::where('patient_id', $patient->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Clinical documents retrieved successfully.',
            'data' => $documents,
        ]);
    }

    public function store(Request $request, int $patientId): JsonResponse
    {
        $patient = Patient::findOrFail($patientId);

        abort_unless(Gate::allows('update', $patient), 403, 'You are not authorized.');

        $data = $request->validate([
            'document' => ['required', 'file', 'max:20480'],
            'name' => ['nullable', 'string', 'max:255'],
            'encounter_id' => ['nullable', 'exists:encounters,id'],
        ]);

        $file = $request->file('document');
        $path = $file->store('clinical_documents');

        $document = ClinicalDocument::create([
            'patient_id' => $patient->id,
            'encounter_id' => $data['encounter_id'] ?? null,
            'uploaded_by' => $request->user()->id,
            'name' => $data['name'] ?? $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clinical document uploaded successfully.',
            'data' => $document,
        ], 201);
    }

    public function show(Request $request, int $patientId, int $id)
    {
        $patient = Patient::findOrFail($patientId);

        abort_unless(Gate::allows('view', $patient), 403, 'You are not authorized.');

        $document = ClinicalDocument::where('patient_id', $patient->id)->findOrFail($id);

        if ($request->boolean('download')) {
            return Storage::download($document->path, $document->name);
        }

        return response()->json([
            'success' => true,
            'message' => 'Clinical document retrieved successfully.',
            'data' => $document,
        ]);
    }
}
