@extends('layouts.hims')

@section('title', 'Inpatient Overview')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Inpatient Overview</h1>
            <p class="text-sm text-slate-500 mt-1">Monitor bed status, occupancy, and recent admissions across the inpatient units.</p>
        </div>
        <a href="{{ route('beds.index') }}" class="px-4 py-2 text-sm bg-slate-800 text-white rounded-lg hover:bg-slate-700">Open Bed Board</a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        @foreach (['AVAILABLE' => ['Available', 'bg-green-100 text-green-700'], 'OCCUPIED' => ['Occupied', 'bg-red-100 text-red-700'], 'RESERVED' => ['Reserved', 'bg-amber-100 text-amber-700'], 'CLEANING' => ['Cleaning', 'bg-blue-100 text-blue-700'], 'MAINTENANCE' => ['Maintenance', 'bg-slate-200 text-slate-700'], 'BLOCKED' => ['Blocked', 'bg-slate-300 text-slate-700']] as $key => [$label, $cls])
            <div class="p-4 rounded-xl border border-slate-200 bg-white">
                <div class="text-2xl font-bold text-slate-800">{{ $bedStats[$key] ?? 0 }}</div>
                <div class="text-xs text-slate-500">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h2 class="font-semibold text-slate-800">Ward summary</h2>
            </div>
            <div class="p-5 space-y-4">
                @foreach ($wards as $ward)
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <h3 class="font-semibold text-slate-800">{{ $ward->name }}</h3>
                                <p class="text-xs text-slate-500">{{ $ward->code }}</p>
                            </div>
                            <span class="text-xs font-medium rounded-full bg-slate-100 px-2 py-1 text-slate-600">{{ $ward->rooms->count() }} rooms</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-sm">
                            <div class="rounded-lg bg-green-50 p-2">
                                <div class="text-lg font-bold text-green-700">{{ $ward->rooms->flatMap(fn ($room) => $room->beds)->filter(fn ($bed) => $bed->status === 'AVAILABLE')->count() }}</div>
                                <div class="text-[10px] uppercase tracking-wide text-green-700">Available</div>
                            </div>
                            <div class="rounded-lg bg-red-50 p-2">
                                <div class="text-lg font-bold text-red-700">{{ $ward->rooms->flatMap(fn ($room) => $room->beds)->filter(fn ($bed) => $bed->status === 'OCCUPIED')->count() }}</div>
                                <div class="text-[10px] uppercase tracking-wide text-red-700">Occupied</div>
                            </div>
                            <div class="rounded-lg bg-amber-50 p-2">
                                <div class="text-lg font-bold text-amber-700">{{ $ward->rooms->flatMap(fn ($room) => $room->beds)->filter(fn ($bed) => $bed->status === 'RESERVED')->count() }}</div>
                                <div class="text-[10px] uppercase tracking-wide text-amber-700">Reserved</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <h2 class="font-semibold text-slate-800">Recent admissions</h2>
                <a href="{{ route('admissions.index') }}" class="text-sm text-teal-700 hover:text-teal-800">View all</a>
            </div>
            <div class="p-5 space-y-3">
                @forelse ($admissions as $admission)
                    <div class="rounded-xl border border-slate-200 p-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-semibold text-slate-800">{{ $admission->patient?->full_name ?? 'Unknown patient' }}</div>
                                <div class="text-xs text-slate-500">{{ $admission->admission_number ?? $admission->id }}</div>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-medium uppercase tracking-wide text-slate-600">{{ $admission->status }}</span>
                        </div>
                        <div class="mt-2 text-xs text-slate-500">
                            @if ($admission->bedAssignments->isNotEmpty())
                                Assigned to {{ $admission->bedAssignments->first()->bed?->label ?? 'bed' }}
                            @else
                                Awaiting bed assignment
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 p-6 text-sm text-slate-500 text-center">No recent admissions found.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
