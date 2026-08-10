@extends('layouts.hims')

@section('title', 'Encounter Detail')

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Encounter {{ $encounter->encounter_number }}</h1>
            <p class="text-sm text-slate-500 mt-1">Patient: <span class="font-medium">{{ $encounter->patient->full_name ?? '—' }}</span> · {{ $encounter->patient->mrn ?? '' }}</p>
        </div>
        <div class="flex gap-2">
            @if ($encounter->status === 'OPEN')
                <form method="POST" action="{{ route('encounters.complete', $encounter) }}" onsubmit="return confirm('Complete this encounter?')">@csrf
                    <button class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">Complete Encounter</button>
                </form>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div><span class="text-slate-500">Provider:</span> <span class="font-medium">{{ $encounter->provider->full_name ?? '—' }}</span></div>
            <div><span class="text-slate-500">Type:</span> <span class="px-2 py-1 text-xs rounded-full bg-slate-100">{{ $encounter->type }}</span></div>
            <div><span class="text-slate-500">Status:</span> <span class="px-2 py-1 text-xs rounded-full bg-slate-100">{{ $encounter->status }}</span></div>
            <div class="col-span-1"><span class="text-slate-500">Started:</span> {{ $encounter->started_at->format('M d, Y g:i A') }}</div>
            @if ($encounter->appointment)
                <div class="col-span-2"><span class="text-slate-500">Appointment:</span> <span class="font-mono">{{ $encounter->appointment->appointment_number }}</span></div>
            @endif
        </div>
    </div>

    @if ($encounter->chief_complaint)
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-800 mb-2">Chief Complaint</h3>
        <p class="text-sm text-slate-600">{{ $encounter->chief_complaint }}</p>
    </div>
    @endif

    @if ($encounter->vitals->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-800 mb-3">Vital Signs</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
            @php $v = $encounter->vitals->last(); @endphp
            <div><span class="text-slate-500">BP</span><div class="font-medium">{{ $v->blood_pressure ?? '—' }}</div></div>
            <div><span class="text-slate-500">HR</span><div class="font-medium">{{ $v->heart_rate ?? '—' }}</div></div>
            <div><span class="text-slate-500">RR</span><div class="font-medium">{{ $v->respiratory_rate ?? '—' }}</div></div>
            <div><span class="text-slate-500">Temp</span><div class="font-medium">{{ $v->temperature ?? '—' }}</div></div>
            <div><span class="text-slate-500">SpO2</span><div class="font-medium">{{ $v->spo2 ?? '—' }}</div></div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-800 mb-2">Assessment</h3>
            <p class="text-sm text-slate-600">{{ $encounter->assessment ?? '—' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-800 mb-2">Plan</h3>
            <p class="text-sm text-slate-600">{{ $encounter->plan ?? '—' }}</p>
        </div>
    </div>

    @if ($encounter->discharge_instructions || $encounter->follow_up_date)
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-800 mb-3">Discharge & follow-up</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            @if ($encounter->discharge_instructions)
                <div class="md:col-span-2">
                    <span class="text-slate-500 block mb-1">Discharge instructions</span>
                    <p class="text-slate-700">{{ $encounter->discharge_instructions }}</p>
                </div>
            @endif
            @if ($encounter->follow_up_date)
                <div>
                    <span class="text-slate-500 block mb-1">Follow-up date</span>
                    <p class="font-medium text-slate-700">{{ $encounter->follow_up_date->format('M d, Y') }}</p>
                </div>
            @endif
        </div>
    </div>
    @endif

    @if ($encounter->notes->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-800 mb-3">Clinical Notes</h3>
        <div class="space-y-3">
            @foreach ($encounter->notes as $note)
                <div class="p-3 bg-slate-50 rounded-lg">
                    <div class="flex justify-between text-xs text-slate-500 mb-1">
                        <span>{{ $note->author->name ?? 'System' }}</span>
                        <span>{{ $note->created_at->format('M d, Y g:i A') }}</span>
                    </div>
                    <p class="text-sm text-slate-700">{{ $note->content }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if ($encounter->status === 'OPEN')
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-800 mb-3">Complete Encounter</h3>
        <form method="POST" action="{{ route('encounters.complete', $encounter) }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Assessment</label><textarea name="assessment" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">{{ $encounter->assessment }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Plan</label><textarea name="plan" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">{{ $encounter->plan }}</textarea></div>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Follow-up Date</label><input type="date" name="follow_up_date" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg md:w-64"></div>
            <button type="submit" class="px-6 py-2.5 text-sm bg-green-600 text-white rounded-lg">Complete</button>
        </form>
    </div>
    @endif
</div>
@endsection
