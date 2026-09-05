@extends('layouts.hims')

@section('title', 'Bed Board')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Bed Board</h1>
            <p class="text-sm text-slate-500 mt-1">Inpatient and Bed Management System (IBMS)</p>
        </div>
        <a href="{{ route('admissions.index') }}" class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700">Admissions</a>
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
                                    <button type="button" class="mt-2 w-full rounded-lg bg-slate-800 px-2 py-1.5 text-[10px] font-semibold text-white hover:bg-slate-700" data-bs-toggle="modal" data-bs-target="#bedStatusModal-{{ $bed->id }}">Update status</button>
                                </div>

                                <div class="modal fade" id="bedStatusModal-{{ $bed->id }}" tabindex="-1" aria-labelledby="bedStatusModalLabel-{{ $bed->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 bg-white shadow-2xl">
                                            <div class="modal-header border-b border-slate-200 bg-white px-5 py-4">
                                                <div>
                                                    <h5 class="modal-title text-lg font-semibold text-slate-900" id="bedStatusModalLabel-{{ $bed->id }}">Update Bed Status</h5>
                                                    <p class="mt-1 text-sm text-slate-500">Bed {{ $bed->number }} · {{ $bed->room->ward->name ?? 'Ward' }}</p>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body bg-white px-5 py-5">
                                                <form method="POST" action="{{ route('beds.status', $bed) }}" class="space-y-4">
                                                    @csrf
                                                    <div>
                                                        <label for="bed-status-{{ $bed->id }}" class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                                                        <select id="bed-status-{{ $bed->id }}" name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                                                            <option value="AVAILABLE" {{ $bed->status === 'AVAILABLE' ? 'selected' : '' }}>Available</option>
                                                            <option value="CLEANING" {{ $bed->status === 'CLEANING' ? 'selected' : '' }}>Cleaning</option>
                                                            <option value="MAINTENANCE" {{ $bed->status === 'MAINTENANCE' ? 'selected' : '' }}>Maintenance</option>
                                                            <option value="BLOCKED" {{ $bed->status === 'BLOCKED' ? 'selected' : '' }}>Blocked</option>
                                                        </select>
                                                    </div>
                                                    <div class="flex items-center justify-end gap-3 pt-2">
                                                        <button type="button" class="rounded-xl border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="rounded-xl bg-slate-800 px-6 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">Save status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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
