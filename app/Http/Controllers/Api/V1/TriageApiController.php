<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ErVisit;
use App\Services\TriageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TriageApiController extends Controller
{
    public function __construct(protected TriageService $triageService)
    {
    }

    /**
     * Score a triage input set without persisting it.
     */
    public function score(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('triage-patients'), 403, 'You are not authorized to perform triage.');

        $data = $request->validate([
            'chief_complaint' => ['nullable', 'string'],
            'pain_score' => ['nullable', 'integer', 'min:0', 'max:10'],
            'visual' => ['nullable', 'array'],
            'visual.breathing' => ['nullable', 'string'],
            'visual.consciousness' => ['nullable', 'string'],
            'symptoms' => ['nullable', 'array'],
            'vitals' => ['nullable', 'array'],
            'vitals.blood_pressure' => ['nullable'],
            'vitals.heart_rate' => ['nullable', 'integer'],
            'vitals.respiratory_rate' => ['nullable', 'integer'],
            'vitals.temperature' => ['nullable', 'numeric'],
            'vitals.spo2' => ['nullable', 'numeric'],
        ]);

        $data['vitals']['blood_pressure'] = $this->normaliseBloodPressureValue($data['vitals']['blood_pressure'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Triage score calculated successfully.',
            'data' => $this->triageService->score($data),
        ]);
    }

    /**
     * Perform triage on an ER visit.
     */
    protected function normaliseBloodPressureValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (string) (float) $value;
        }

        if (preg_match('/^\d{2,3}\/\d{2,3}$/', $value)) {
            return $value;
        }

        if (preg_match('/^(\d{2,3})\s*\/\s*(\d{2,3})$/', $value, $matches)) {
            return $matches[1] . '/' . $matches[2];
        }

        return $value;
    }

    public function store(Request $request, int $id): JsonResponse
    {
        abort_unless(Gate::allows('triage-patients'), 403, 'You are not authorized to perform triage.');

        $visit = ErVisit::findOrFail($id);

        $data = $request->validate([
            'chief_complaint' => ['nullable', 'string'],
            'pain_score' => ['nullable', 'integer', 'min:0', 'max:10'],
            'priority' => ['required', 'in:Level 1,Level 2,Level 3,Level 4,Level 5'],
            'notes' => ['nullable', 'string'],
            'treatment_area' => ['nullable', 'string'],
            'provider_id' => ['nullable', 'exists:providers,id'],
            'vitals' => ['nullable', 'array'],
            'vitals.blood_pressure' => ['nullable'],
            'vitals.heart_rate' => ['nullable', 'integer'],
            'vitals.respiratory_rate' => ['nullable', 'integer'],
            'vitals.temperature' => ['nullable', 'numeric'],
            'vitals.spo2' => ['nullable', 'numeric'],
            'vitals.weight' => ['nullable', 'numeric'],
        ]);

        if (isset($data['vitals']['blood_pressure'])) {
            $data['vitals']['blood_pressure'] = $this->normaliseBloodPressureValue($data['vitals']['blood_pressure']);
        }

        $assessment = $this->triageService->triage($visit, $data);

        return response()->json([
            'success' => true,
            'message' => 'Triage recorded successfully.',
            'data' => $assessment->load('vitals'),
        ], 201);
    }
}
