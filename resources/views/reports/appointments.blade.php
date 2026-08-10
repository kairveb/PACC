@extends('layouts.hims')

@section('title', 'Appointment Volume Report')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-slate-800">Appointment Report</h1><p class="text-sm text-slate-500 mt-1">{{ $start }} to {{ $end }}</p></div>
        <a href="{{ route('reports.index') }}" class="px-4 py-2 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">Back</a>
    </div>

    @include('reports._date-form')

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-5 bg-white rounded-xl border border-slate-200"><div class="text-3xl font-bold">{{ $total }}</div><div class="text-sm text-slate-500">Total</div></div>
        <div class="p-5 bg-white rounded-xl border border-slate-200"><div class="text-3xl font-bold text-red-600">{{ $cancelled }}</div><div class="text-sm text-slate-500">Cancelled</div></div>
        <div class="p-5 bg-white rounded-xl border border-slate-200"><div class="text-3xl font-bold text-amber-600">{{ $noShow }}</div><div class="text-sm text-slate-500">No-show</div></div>
        <div class="p-5 bg-white rounded-xl border border-slate-200"><div class="text-3xl font-bold text-teal-600">{{ $total - $cancelled - $noShow }}</div><div class="text-sm text-slate-500">Completed/Active</div></div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 font-semibold">By Status</div>
        <div class="p-5">
            @foreach ($byStatus as $status => $count)
                <div class="flex items-center gap-3 py-1.5 text-sm">
                    <span class="w-40 text-slate-600">{{ $status }}</span>
                    <div class="flex-1 h-6 bg-slate-100 rounded overflow-hidden"><div class="h-full bg-teal-500" style="width: {{ max($count / max($total,1) * 100, 4) }}%"></div></div>
                    <span class="font-medium w-8 text-right">{{ $count }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
