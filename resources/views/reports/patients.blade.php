@extends('layouts.hims')

@section('title', 'Patient Registrations Report')

@section('content')
<div class="space-y-6">
<div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-slate-800">Patient Registration Report</h1><p class="text-sm text-slate-500 mt-1">{{ $start }} to {{ $end }}</p></div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm bg-slate-800 text-white rounded-lg hover:bg-slate-900">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @include('reports._date-form')

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-5 bg-white rounded-xl border border-slate-200"><div class="text-3xl font-bold">{{ $data->count() }}</div><div class="text-sm text-slate-500">Total registrations</div></div>
        <div class="p-5 bg-white rounded-xl border border-slate-200"><div class="text-3xl font-bold">{{ $data->count() ? round($data->count() / max(count($byDay),1),1) : 0 }}</div><div class="text-sm text-slate-500">Per day (avg)</div></div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 font-semibold">Registrations by Day</div>
        <div class="p-5">
            @foreach ($byDay as $date => $count)
                <div class="flex items-center gap-3 py-1.5 text-sm">
                    <span class="w-32 text-slate-600">{{ $date }}</span>
                    <div class="flex-1 h-6 bg-slate-100 rounded overflow-hidden"><div class="h-full bg-teal-500" style="width: {{ max($count / max($data->count(),1) * 100, 4) }}%"></div></div>
                    <span class="font-medium w-8 text-right">{{ $count }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
