@extends('layouts.hims')

@section('title', 'Outpatient Encounters')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Outpatient Encounters</h1>
            <p class="text-sm text-slate-500 mt-1">Telehealth and Outpatient Care System (TOCS)</p>
        </div>
        <a href="{{ route('encounters.create') }}" class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700">New Encounter</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                        <th class="py-3 px-4">Number</th><th class="py-3 px-4">Patient</th><th class="py-3 px-4">Provider</th><th class="py-3 px-4">Date/Time</th><th class="py-3 px-4">Type</th><th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($encounters as $enc)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="py-3 px-4 font-mono text-xs">{{ $enc->encounter_number }}</td>
                            <td class="py-3 px-4 font-medium"><a href="{{ route('patients.show', $enc->patient) }}" class="text-teal-600 hover:underline">{{ $enc->patient->full_name ?? '—' }}</a></td>
                            <td class="py-3 px-4">{{ $enc->provider->full_name ?? '—' }}</td>
                            <td class="py-3 px-4">{{ $enc->started_at?->format('M d, Y g:i A') ?? '—' }}</td>
                            <td class="py-3 px-4">{{ $enc->type }}</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 text-xs rounded-full bg-slate-100">{{ $enc->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-400">No encounters found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">{{ $encounters->links() }}</div>
    </div>
</div>
@endsection
