<?php

namespace App\Http\Controllers;

use App\Models\TriageAssessment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorQueueController extends Controller
{
    public function index(Request $request): View
    {
        $query = TriageAssessment::with(['patient', 'vitals'])
            ->whereNotNull('patient_id');

        $user = auth()->user();
        if ($user && $user->hasRole('doctor')) {
            $query->whereIn('status', ['WAITING', 'SEEN', 'IN_CONSULT'])
                ->whereNotNull('priority_score');
        }

        if ($request->filled('q')) {
            $term = trim($request->q);
            $query->where(function ($patientQuery) use ($term) {
                $patientQuery->whereHas('patient', function ($patient) use ($term) {
                    $patient->where(function ($nameQuery) use ($term) {
                        $nameQuery->where('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%")
                            ->orWhere('mrn', 'like', "%{$term}%");
                    });
                });
            });
        }

        if ($request->filled('priority')) {
            $query->where('priority_score', (int) $request->priority);
        }

        $queue = $query->orderByRaw("CASE
                WHEN priority_score IS NULL THEN 99
                WHEN priority_score = 1 THEN 1
                WHEN priority_score = 2 THEN 2
                WHEN priority_score = 3 THEN 3
                WHEN priority_score = 4 THEN 4
                ELSE 5
            END ASC")
            ->orderByDesc('triaged_at')
            ->get();

        $summary = [
            'level_1' => $queue->where('priority_score', 1)->count(),
            'level_2' => $queue->where('priority_score', 2)->count(),
            'level_3' => $queue->where('priority_score', 3)->count(),
            'total' => $queue->count(),
        ];

        return view('doctors.queue', compact('queue', 'summary'));
    }

    public function show(TriageAssessment $assessment): View
    {
        $assessment->load(['patient', 'vitals', 'triageNurse']);

        return view('doctors.show', compact('assessment'));
    }

    public function updateStatus(Request $request, TriageAssessment $assessment): RedirectResponse
    {
        $status = $request->validate([
            'status' => ['required', 'in:SEEN,IN_CONSULT,COMPLETED'],
        ])['status'];

        $assessment->update([
            'status' => $status,
        ]);

        $label = match ($status) {
            'SEEN' => 'seen',
            'IN_CONSULT' => 'consult started',
            'COMPLETED' => 'consult completed',
            default => 'updated',
        };

        return back()->with('success', 'Patient marked as ' . $label . '.');
    }
}
