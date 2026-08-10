@extends('layouts.hims')

@section('title', 'Outpatient Encounters Report')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-slate-800">Outpatient Encounter Report</h1><p class="text-sm text-slate-500 mt-1">{{ $start }} to {{ $end }}</p></div>
        <a href="{{ route('reports.index') }}" class="px-4 py-2 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">Back</a>
    </div>

    @include('reports._date-form')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-5 bg-white rounded-xl border border-slate-200"><div class="text-3xl font-bold">{{ $encounters->count() }}</div><div class="text-sm text-slate-500">Total encounters</div></div>
        <div class="p-5 bg-white rounded-xl border border-slate-200">
            <div class="text-sm text-slate-500 mb-2">By Type</div>
            @foreach ($byType as $type => $count)
                <div class="flex items-center gap-3 py-1 text-sm">
                    <span class="w-32 text-slate-600">{{ $type }}</span>
                    <div class="flex-1 h-5 bg-slate-100 rounded overflow-hidden"><div class="h-full bg-teal-500" style="width: {{ max($count / max($encounters->count(),1) * 100, 4) }}%"></div></div>
                    <span class="font-medium w-8 text-right">{{ $count }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
