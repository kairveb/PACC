@extends('layouts.hims')

@section('title', 'ER Visit')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">ER Visit {{ $visit->visit_number }}</h1>
        <p class="text-sm text-slate-500 mt-1">Patient: <span class="font-medium">{{ $visit->patient->full_name ?? '—' }}</span> · {{ $visit->patient->mrn ?? '' }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Visit details --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-800 mb-4">Arrival Information</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-slate-500">Arrived:</span> <span class="font-medium">{{ $visit->arrived_at->format('M d, Y g:i A') }}</span></div>
                <div><span class="text-slate-500">Method:</span> <span class="font-medium">{{ $visit->arrival_method ?? '—' }}</span></div>
                <div class="col-span-2"><span class="text-slate-500">Complaint:</span> <span class="font-medium">{{ $visit->chief_complaint }}</span></div>
                <div class="col-span-2"><span class="text-slate-500">Status:</span> <span class="px-2 py-1 text-xs rounded-full bg-slate-100 ml-1">{{ $visit->status }}</span></div>
            </div>

            @if ($visit->queue)
            <div class="mt-4 p-3 bg-slate-50 rounded-lg">
                <h4 class="text-sm font-medium text-slate-700 mb-2">ER Queue</h4>
                <div class="flex justify-between text-sm">
                    <span>Priority: <strong>{{ $visit->queue->priority }}</strong></span>
                    <span>Status: {{ $visit->queue->status }}</span>
                    <span>Area: {{ $visit->queue->treatment_area ?? '—' }}</span>
                </div>
                <form method="POST" action="{{ route('emergency.queue-status', $visit->queue) }}" class="mt-3 flex gap-2">
                    @csrf
                    <select name="status" class="px-3 py-2 text-sm border border-slate-300 rounded-lg">
                        <option value="WAITING" {{ $visit->queue->status === 'WAITING' ? 'selected' : '' }}>Waiting</option>
                        <option value="IN_TREATMENT" {{ $visit->queue->status === 'IN_TREATMENT' ? 'selected' : '' }}>In Treatment</option>
                        <option value="DONE" {{ $visit->queue->status === 'DONE' ? 'selected' : '' }}>Done</option>
                    </select>
                    <select name="provider_id" class="px-3 py-2 text-sm border border-slate-300 rounded-lg">
                        <option value="">Provider</option>
                        @foreach ($providers as $provider)
                            <option value="{{ $provider->id }}">{{ $provider->full_name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 text-sm bg-slate-800 text-white rounded-lg">Update</button>
                </form>
            </div>
            @endif
        </div>

        {{-- Triage form --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-800 mb-4">Triage Assessment</h3>
            <form method="POST" action="{{ route('emergency.triage', $visit) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Chief Complaint</label>
                    <textarea name="chief_complaint" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">{{ $visit->chief_complaint }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Pain Score (0-10)</label>
                        <input type="number" name="pain_score" min="0" max="10" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Priority *</label>
                        <select name="priority" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                            <option value="">Select</option>
                            <option value="Level 1">Level 1 — Critical</option>
                            <option value="Level 2">Level 2</option>
                            <option value="Level 3">Level 3</option>
                            <option value="Level 4">Level 4</option>
                            <option value="Level 5">Level 5 — Non-urgent</option>
                        </select>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-slate-700 mb-2">Vital Signs</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-xs text-slate-500 mb-1">Blood Pressure</label><input name="vitals_blood_pressure" placeholder="120/80" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                        <div><label class="block text-xs text-slate-500 mb-1">Heart Rate</label><input type="number" name="vitals_heart_rate" placeholder="bpm" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                        <div><label class="block text-xs text-slate-500 mb-1">Respiratory Rate</label><input type="number" name="vitals_respiratory_rate" placeholder="/min" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                        <div><label class="block text-xs text-slate-500 mb-1">Temperature (°C)</label><input type="number" step="0.1" name="vitals_temperature" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                        <div><label class="block text-xs text-slate-500 mb-1">SpO2 (%)</label><input type="number" name="vitals_spo2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Treatment Area</label>
                    <input type="text" name="treatment_area" placeholder="Resus, Bay 1..." class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></textarea>
                </div>

                <button type="submit" class="px-6 py-2.5 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700">Complete Triage</button>
            </form>
        </div>
    </div>

    {{-- Triage history --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200">
            <h3 class="font-semibold text-slate-800">Triage History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                        <th class="py-3 px-4">Time</th><th class="py-3 px-4">Priority</th><th class="py-3 px-4">Pain</th><th class="py-3 px-4">Vitals</th><th class="py-3 px-4">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visit->triageAssessments as $ta)
                        <tr class="border-b border-slate-100">
                            <td class="py-3 px-4">{{ $ta->triaged_at->format('M d, g:i A') }}</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">{{ $ta->priority }}</span></td>
                            <td class="py-3 px-4">{{ $ta->pain_score ?? '—' }}/10</td>
                            <td class="py-3 px-4 text-xs">
                                @if ($ta->triageVital)
                                    BP {{ $ta->triageVital->blood_pressure ?? '—' }} · HR {{ $ta->triageVital->heart_rate ?? '—' }} · T {{ $ta->triageVital->temperature ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="py-3 px-4">{{ $ta->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-400">No triage assessments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
