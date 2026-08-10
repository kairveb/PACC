@extends('layouts.hims')

@section('title', 'Bed Occupancy Report')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-slate-800">Bed Occupancy Report</h1><p class="text-sm text-slate-500 mt-1">Current bed status</p></div>
        <a href="{{ route('reports.index') }}" class="px-4 py-2 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">Back</a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        @foreach (['AVAILABLE'=>'green','OCCUPIED'=>'red','RESERVED'=>'amber','CLEANING'=>'blue','MAINTENANCE'=>'slate','BLOCKED'=>'slate'] as $key=>$color)
            <div class="p-5 bg-white rounded-xl border border-slate-200">
                <div class="text-3xl font-bold text-{{ $color }}-600">{{ $beds[$key] ?? 0 }}</div>
                <div class="text-sm text-slate-500">{{ $key }}</div>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 font-semibold">Currently Admitted Patients</div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-xs uppercase text-slate-500 bg-slate-50 border-b border-slate-200"><th class="py-3 px-4">Patient</th><th class="py-3 px-4">Admission</th><th class="py-3 px-4">Admitted At</th></tr></thead>
                <tbody>
                    @forelse ($admissions as $adm)
                        <tr class="border-b border-slate-100">
                            <td class="py-3 px-4 font-medium">{{ $adm->patient->full_name ?? '—' }}</td>
                            <td class="py-3 px-4 font-mono text-xs">{{ $adm->admission_number }}</td>
                            <td class="py-3 px-4">{{ $adm->admitted_at?->format('M d, Y g:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-slate-400">No currently admitted patients.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
