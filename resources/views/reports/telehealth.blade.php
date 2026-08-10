@extends('layouts.hims')

@section('title', 'Telehealth Usage Report')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-slate-800">Telehealth Usage Report</h1><p class="text-sm text-slate-500 mt-1">Recent sessions</p></div>
        <a href="{{ route('reports.index') }}" class="px-4 py-2 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">Back</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="text-sm text-slate-500 mb-2">By Status</div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach ($byStatus as $status => $count)
                <div class="p-4 rounded-lg border border-slate-200"><div class="text-2xl font-bold">{{ $count }}</div><div class="text-xs text-slate-500">{{ $status }}</div></div>
            @endforeach
        </div>
    </div>
</div>
@endsection
