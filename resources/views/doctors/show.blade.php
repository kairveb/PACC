@extends('layouts.hims')

@section('title', $assessment->patient?->full_name . ' Triage Detail')
@section('page-kicker', 'Clinical review')
@section('page-title', $assessment->patient?->full_name ?? 'Patient triage detail')
@section('page-badge', 'Priority review')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <div class="panel-card p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="text-sm uppercase tracking-[0.2em] text-slate-500">Triage record</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ $assessment->patient?->full_name ?? 'Unknown patient' }}</h2>
                <p class="mt-1 text-sm text-slate-600">MRN: {{ $assessment->patient?->mrn ?? '—' }} · {{ $assessment->triaged_at?->format('M d, Y g:i A') ?? '—' }}</p>
            </div>
            <div class="flex items-center gap-3">
                @php
                    $priorityScore = (int) ($assessment->priority_score ?? 5);
                    $priorityLabel = match ($priorityScore) {
                        1 => 'Emergency',
                        2 => 'Urgent',
                        3 => 'Prompt',
                        4 => 'Non-Urgent',
                        default => 'Routine',
                    };
                    $pill = match ($priorityScore) {
                        1 => 'danger',
                        2 => 'warning',
                        3 => 'info',
                        default => 'success',
                    };
                @endphp
                <span class="status-pill {{ $pill }}">{{ $priorityLabel }}</span>
                <a href="{{ route('doctors.queue') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Back to queue</a>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-6">
            <div class="panel-card p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">Clinical summary</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-500">Chief complaint</div>
                        <div class="mt-2 text-base font-medium text-slate-800">{{ $assessment->chief_complaint ?? 'No complaint recorded' }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-500">Pain score</div>
                        <div class="mt-2 text-base font-medium text-slate-800">{{ $assessment->pain_score ?? '—' }}/10</div>
                    </div>
                    <div class="md:col-span-2">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Symptoms</div>
                        <div class="mt-2 text-base text-slate-700">
                            {{ !empty($assessment->symptoms) ? (is_array($assessment->symptoms) ? implode(', ', $assessment->symptoms) : $assessment->symptoms) : 'No symptoms recorded' }}
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Nurse notes</div>
                        <div class="mt-2 text-base text-slate-700">{{ $assessment->notes ?? 'No additional nurse notes recorded.' }}</div>
                    </div>
                </div>
            </div>

            <div class="panel-card p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">Vital signs</h3>
                @if ($assessment->vitals)
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs uppercase tracking-wide text-slate-500">BP</div>
                            <div class="mt-2 text-xl font-semibold text-slate-900">{{ $assessment->vitals->blood_pressure ?? '—' }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs uppercase tracking-wide text-slate-500">HR</div>
                            <div class="mt-2 text-xl font-semibold text-slate-900">{{ $assessment->vitals->heart_rate ?? '—' }} bpm</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs uppercase tracking-wide text-slate-500">RR</div>
                            <div class="mt-2 text-xl font-semibold text-slate-900">{{ $assessment->vitals->respiratory_rate ?? '—' }}/min</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs uppercase tracking-wide text-slate-500">Temp</div>
                            <div class="mt-2 text-xl font-semibold text-slate-900">{{ $assessment->vitals->temperature ?? '—' }}°C</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs uppercase tracking-wide text-slate-500">SpO₂</div>
                            <div class="mt-2 text-xl font-semibold text-slate-900">{{ $assessment->vitals->spo2 ?? '—' }}%</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs uppercase tracking-wide text-slate-500">Weight</div>
                            <div class="mt-2 text-xl font-semibold text-slate-900">{{ $assessment->vitals->weight ?? '—' }} kg</div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-slate-500">No vital signs captured for this assessment.</p>
                @endif
            </div>
        </div>

        <aside class="space-y-6">
            <div class="panel-card p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">AI recommendation</h3>
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <span class="status-pill {{ $pill }}">{{ $priorityLabel }}</span>
                        <span class="text-xs text-slate-500">Priority score: {{ $assessment->priority_score ?? '—' }}</span>
                    </div>
                    <p class="text-sm text-slate-700">{{ $assessment->notes ?? 'No recommendation text was stored for this triage record.' }}</p>
                </div>
            </div>

            <div class="panel-card p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">Consult status</h3>
                <div class="flex flex-wrap items-center gap-3">
                    @php
                        $consultStatusValue = $assessment->status ?? null;
                        $consultStatusLabel = match ($consultStatusValue) {
                            'SEEN' => 'Seen',
                            'IN_CONSULT' => 'In consult',
                            'COMPLETED' => 'Completed',
                            default => 'Waiting',
                        };
                        $consultStatusClass = match ($consultStatusValue) {
                            'SEEN' => 'success',
                            'IN_CONSULT' => 'warning',
                            'COMPLETED' => 'info',
                            default => 'success',
                        };
                    @endphp
                    @if (($assessment->status ?? '') !== 'SEEN' && ($assessment->status ?? '') !== 'IN_CONSULT')
                        <form method="POST" action="{{ route('doctors.queue.status', $assessment) }}">
                            @csrf
                            <input type="hidden" name="status" value="SEEN">
                            <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Mark seen</button>
                        </form>
                    @endif
                    @if (($assessment->status ?? '') !== 'IN_CONSULT' && ($assessment->status ?? '') !== 'COMPLETED')
                        <form method="POST" action="{{ route('doctors.queue.status', $assessment) }}">
                            @csrf
                            <input type="hidden" name="status" value="IN_CONSULT">
                            <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">Start consult</button>
                        </form>
                    @endif
                    @if (($assessment->status ?? '') === 'IN_CONSULT')
                        <form method="POST" action="{{ route('doctors.queue.status', $assessment) }}">
                            @csrf
                            <input type="hidden" name="status" value="COMPLETED">
                            <button type="submit" class="rounded-xl bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Complete consult</button>
                        </form>
                    @endif
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                        Status: <span class="status-pill {{ $consultStatusClass }}">{{ $consultStatusLabel }}</span>
                    </div>
                </div>
            </div>

            <div class="panel-card p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">Clinical metadata</h3>
                <dl class="space-y-3 text-sm text-slate-700">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Assessment ID</dt>
                        <dd class="font-medium text-slate-900">#{{ $assessment->id }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Triage nurse</dt>
                        <dd class="font-medium text-slate-900">{{ $assessment->triageNurse?->name ?? 'Unassigned' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Triage color</dt>
                        <dd class="font-medium text-slate-900 uppercase">{{ $assessment->triage_color ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Status</dt>
                        <dd class="font-medium text-slate-900">{{ $assessment->status ?? 'COMPLETE' }}</dd>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
</div>
@endsection
