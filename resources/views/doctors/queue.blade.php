@extends('layouts.hims')

@section('title', 'Doctor Urgency Queue')
@section('page-kicker', 'Clinical triage')
@section('page-title', 'Doctor Urgency Queue')
@section('page-badge', 'Priority board')

@section('content')
<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-4">
        <div class="panel-card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-rose-600">Level 1</div>
                <span class="status-pill danger">Red</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ $summary['level_1'] ?? 0 }}</div>
            <p class="mt-2 text-sm text-slate-500">Emergency</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-amber-600">Level 2</div>
                <span class="status-pill warning">Yellow</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ $summary['level_2'] ?? 0 }}</div>
            <p class="mt-2 text-sm text-slate-500">Urgent</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-orange-600">Level 3</div>
                <span class="status-pill info">Orange</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ $summary['level_3'] ?? 0 }}</div>
            <p class="mt-2 text-sm text-slate-500">Prompt</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-700">Total</div>
                <span class="status-pill success">Live</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ $summary['total'] ?? 0 }}</div>
            <p class="mt-2 text-sm text-slate-500">Awaiting review</p>
        </div>
    </div>

    @include('partials.status-legend')

    <div class="panel-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Incoming patient priority queue</h2>
                <p class="text-sm text-slate-600">Patients are automatically ranked by AI triage urgency.</p>
            </div>
            <a href="{{ route('triage.create') }}" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">New triage</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Priority</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Patient</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Complaint</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Vitals</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Pain</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Time</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($queue as $assessment)
                        @php
                            $priorityScore = (int) ($assessment->priority_score ?? 5);
                            $priorityLabel = App\Support\PriorityColor::label($priorityScore);
                            $colorClass = App\Support\PriorityColor::variant($priorityScore);
                            $statusValue = $assessment->status ?? null;
                            $statusBadgeClass = App\Support\QueueStatus::variant($statusValue);
                            $statusLabel = App\Support\QueueStatus::label($statusValue);
                            $rowClass = App\Support\QueueStatus::rowClass($statusValue);
                            $vitals = $assessment->vitals;
                        @endphp
                        <tr class="border-t border-slate-200 align-top {{ $rowClass }}">
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-2">
                                    <span class="status-pill {{ $colorClass }}">{{ $priorityLabel }}</span>
                                    @if ($statusValue)
                                        <span class="status-pill {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900">{{ $assessment->patient?->full_name ?? 'Unknown patient' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $assessment->patient?->mrn ?? '—' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="max-w-md text-sm text-slate-700">{{ $assessment->chief_complaint ?? 'No complaint recorded' }}</div>
                                @if (!empty($assessment->symptoms))
                                    <div class="mt-2 text-xs text-slate-500">{{ is_array($assessment->symptoms) ? implode(', ', $assessment->symptoms) : $assessment->symptoms }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($vitals)
                                    <div class="space-y-1 text-sm text-slate-700">
                                        <div>BP: {{ $vitals->blood_pressure ?? '—' }}</div>
                                        <div>HR: {{ $vitals->heart_rate ?? '—' }} bpm</div>
                                        <div>RR: {{ $vitals->respiratory_rate ?? '—' }}/min</div>
                                        <div>Temp: {{ $vitals->temperature ?? '—' }}°C</div>
                                        <div>SpO₂: {{ $vitals->spo2 ?? '—' }}%</div>
                                    </div>
                                @else
                                    <span class="text-sm text-slate-400">No vitals recorded</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm font-medium text-slate-800">{{ $assessment->pain_score ?? '—' }}/10</span>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $assessment->triaged_at?->diffForHumans() ?? 'Recently' }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('doctors.queue.show', $assessment) }}" class="text-sm font-semibold text-teal-600 hover:text-teal-700">Open detail</a>
                                        <a href="{{ route('patients.show', $assessment->patient_id) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-700">Chart</a>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if (($assessment->status ?? '') !== 'SEEN' && ($assessment->status ?? '') !== 'IN_CONSULT')
                                            <form method="POST" action="{{ route('doctors.queue.status', $assessment) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="SEEN">
                                                <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Mark seen</button>
                                            </form>
                                        @endif
                                        @if (($assessment->status ?? '') !== 'IN_CONSULT' && ($assessment->status ?? '') !== 'COMPLETED')
                                            <form method="POST" action="{{ route('doctors.queue.status', $assessment) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="IN_CONSULT">
                                                <button type="submit" class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600">Start consult</button>
                                            </form>
                                        @endif
                                        @if (($assessment->status ?? '') === 'IN_CONSULT')
                                            <form method="POST" action="{{ route('doctors.queue.status', $assessment) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="COMPLETED">
                                                <button type="submit" class="rounded-lg bg-slate-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800">Complete consult</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-sm text-slate-400">No triage records are currently in the queue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const intervalMs = 20000;
        let lastRefresh = Date.now();

        const timer = setInterval(function () {
            if (document.visibilityState === 'visible' && Date.now() - lastRefresh >= intervalMs) {
                lastRefresh = Date.now();
                window.location.reload();
            }
        }, 5000);

        window.addEventListener('beforeunload', function () {
            clearInterval(timer);
        });
    })();
</script>
@endpush
@endsection
