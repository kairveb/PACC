@extends('layouts.hims')

@section('title', 'Vitals - '.$patient->full_name)

@section('content')
<div class="max-w-5xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Vital Signs</h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ $patient->full_name }}
                <span class="font-mono text-xs text-slate-400">· {{ $patient->mrn }}</span>
            </p>
        </div>
        <a href="{{ route('patients.show', $patient) }}"
           class="px-4 py-2 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">
            ← Back to Patient
        </a>
    </div>

    @forelse ($vitals as $vital)
        @php
            $badge = match(true) {
                $vital->pain_score !== null && $vital->pain_score >= 7 => 'red',
                $vital->pain_score !== null && $vital->pain_score >= 4 => 'amber',
                default => 'green',
            };
        @endphp
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <span class="text-sm font-semibold text-slate-700">Vitals</span>
                    @if ($vital->encounter)
                        <span class="ml-2 text-xs text-slate-400">
                            {{ optional($vital->encounter->provider)->full_name ?? 'Encounter' }} ·
                            {{ $vital->encounter->encounter_number }}
                        </span>
                    @endif
                </div>
                <span class="text-xs text-slate-400">{{ $vital->recorded_at?->format('M d, Y h:i A') }}</span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 text-center">
                <div class="rounded-lg bg-slate-50 p-3">
                    <div class="text-xs text-slate-500 uppercase tracking-wide">Blood Pressure</div>
                    <div class="text-lg font-semibold text-slate-800 mt-1">{{ $vital->blood_pressure ?? '—' }}</div>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <div class="text-xs text-slate-500 uppercase tracking-wide">Heart Rate</div>
                    <div class="text-lg font-semibold text-slate-800 mt-1">{{ $vital->heart_rate ?? '—' }} <span class="text-xs text-slate-400">bpm</span></div>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <div class="text-xs text-slate-500 uppercase tracking-wide">Resp. Rate</div>
                    <div class="text-lg font-semibold text-slate-800 mt-1">{{ $vital->respiratory_rate ?? '—' }} <span class="text-xs text-slate-400">/min</span></div>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <div class="text-xs text-slate-500 uppercase tracking-wide">Temperature</div>
                    <div class="text-lg font-semibold text-slate-800 mt-1">{{ $vital->temperature ?? '—' }} <span class="text-xs text-slate-400">°C</span></div>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <div class="text-xs text-slate-500 uppercase tracking-wide">SpO2</div>
                    <div class="text-lg font-semibold text-slate-800 mt-1">{{ $vital->spo2 ?? '—' }} <span class="text-xs text-slate-400">%</span></div>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <div class="text-xs text-slate-500 uppercase tracking-wide">Weight</div>
                    <div class="text-lg font-semibold text-slate-800 mt-1">{{ $vital->weight ?? '—' }} <span class="text-xs text-slate-400">kg</span></div>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <div class="text-xs text-slate-500 uppercase tracking-wide">Pain Score</div>
                    <div class="text-lg font-semibold mt-1
                        {{ $badge === 'red' ? 'text-red-600' : ($badge === 'amber' ? 'text-amber-600' : 'text-slate-800') }}">
                        {{ $vital->pain_score ?? '—' }} <span class="text-xs text-slate-400">/10</span>
                    </div>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <div class="text-xs text-slate-500 uppercase tracking-wide">Recorded By</div>
                    <div class="text-sm font-semibold text-slate-800 mt-1">{{ $vital->recordedBy->name ?? '—' }}</div>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-slate-200 p-10 text-center">
            <p class="text-slate-500">No vital signs recorded for this patient yet.</p>
        </div>
    @endforelse

    <div class="mt-4">
        {{ $vitals->links() }}
    </div>
</div>
@endsection
