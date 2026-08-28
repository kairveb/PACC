@extends('layouts.hims')

@section('title', 'Bed Board')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Bed Board</h1>
            <p class="text-sm text-slate-500 mt-1">Inpatient and Bed Management System (IBMS)</p>
        </div>
        <a href="{{ route('admissions.create') }}" class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700">New Admission</a>
    </div>

    {{-- Bed status summary --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        @foreach (['AVAILABLE' => ['Available', 'bg-green-100 text-green-700'], 'OCCUPIED' => ['Occupied', 'bg-red-100 text-red-700'], 'RESERVED' => ['Reserved', 'bg-amber-100 text-amber-700'], 'CLEANING' => ['Cleaning', 'bg-blue-100 text-blue-700'], 'MAINTENANCE' => ['Maintenance', 'bg-slate-200 text-slate-700'], 'BLOCKED' => ['Blocked', 'bg-slate-300 text-slate-700']] as $key => [$label, $cls])
            <div class="p-4 rounded-xl border border-slate-200 bg-white">
                <div class="text-2xl font-bold text-slate-800">{{ $bedStats[$key] ?? 0 }}</div>
                <div class="text-xs text-slate-500">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    {{-- Wards --}}
    @foreach ($wards as $ward)
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-slate-800">{{ $ward->name }} <span class="text-slate-400 font-normal">({{ $ward->code }})</span></h2>
                    <p class="text-xs text-slate-500">{{ $ward->rooms->count() }} rooms · {{ $ward->rooms->sum(fn ($r) => $r->beds->count()) }} beds</p>
                </div>
            </div>
            <div class="p-5 space-y-4">
                @foreach ($ward->rooms as $room)
                    <div>
                        <div class="text-sm font-medium text-slate-600 mb-2">Room {{ $room->number }}</div>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                            @foreach ($room->beds as $bed)
                                @php
                                    $activeAssignment = $bed->activeAssignment;
                                    $patient = $activeAssignment?->admission?->patient;
                                    $displayPatient = $bed->status === 'OCCUPIED' ? ($patient?->full_name ?? '—') : 'Available';
                                @endphp
                                <div class="p-3 rounded-lg border text-sm
                                    {{ $bed->status === 'AVAILABLE' ? 'bg-green-50 border-green-200' : '' }}
                                    {{ $bed->status === 'OCCUPIED' ? 'bg-red-50 border-red-200' : '' }}
                                    {{ $bed->status === 'RESERVED' ? 'bg-amber-50 border-amber-200' : '' }}
                                    {{ $bed->status === 'CLEANING' ? 'bg-blue-50 border-blue-200' : '' }}
                                    {{ $bed->status === 'MAINTENANCE' || $bed->status === 'BLOCKED' ? 'bg-slate-100 border-slate-200' : '' }}
                                ">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold">Bed {{ $bed->number }}</span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-white border">{{ $bed->status }}</span>
                                    </div>
                                    <div class="mt-1 text-xs text-slate-600 truncate">{{ $displayPatient }}</div>
                                    <form method="POST" action="{{ route('beds.status', $bed) }}" class="mt-2 inline-flex gap-1">
                                        @csrf
                                        <select name="status" class="text-[10px] border border-slate-300 rounded px-1 py-0.5">
                                            <option value="AVAILABLE">Available</option>
                                            <option value="CLEANING">Cleaning</option>
                                            <option value="MAINTENANCE">Maintenance</option>
                                            <option value="BLOCKED">Blocked</option>
                                        </select>
                                        <button type="submit" class="text-[10px] bg-slate-800 text-white rounded px-1.5 py-0.5">Set</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection
