@extends('layouts.hims')

@section('title', 'Appointments')
@section('page-kicker', 'Scheduling')
@section('page-title', 'Appointments')
@section('page-badge', 'ASS')

@section('content')
<div class="space-y-6">
    <div class="panel-card p-5">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Appointment #, patient name..." class="flex-1 min-w-[200px]">
            <input type="date" name="date" value="{{ request('date') }}">
            <select name="status">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
            <select name="follow_up">
                <option value="">All follow-up states</option>
                <option value="due" {{ request('follow_up') === 'due' ? 'selected' : '' }}>Follow-up due</option>
            </select>
            <button type="submit" class="bg-slate-900">Filter</button>
            <a href="{{ route('appointments.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
        </form>
    </div>

    <div class="panel-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Appointment schedule</h2>
                <p class="text-sm text-slate-600">Appointment and Scheduling System (ASS)</p>
            </div>
            <button type="button" class="rounded-2xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700" data-bs-toggle="modal" data-bs-target="#appointmentModal">Book Appointment</button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="text-left">Number</th>
                        <th class="text-left">Patient</th>
                        <th class="text-left">Provider</th>
                        <th class="text-left">Date/Time</th>
                        <th class="text-left">Type</th>
                        <th class="text-left">Status</th>
                        <th class="text-left"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $apt)
                        <tr>
                            <td class="font-mono text-xs">{{ $apt->appointment_number }}</td>
                            <td class="font-medium text-slate-900">
                                <div>{{ $apt->patient->full_name ?? '—' }}</div>
                                @if ($apt->encounter?->follow_up_date)
                                    <div class="mt-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-800">Follow-up: {{ $apt->encounter->follow_up_date->format('M d, Y') }}</div>
                                @endif
                            </td>
                            <td>{{ $apt->provider->full_name ?? '—' }}</td>
                            <td>{{ $apt->starts_at->format('M d, Y g:i A') }}</td>
                            <td>{{ $apt->appointmentType->name ?? '—' }}</td>
                            <td>
                                @include('partials.status-badge', ['label' => $apt->status, 'variant' => App\Support\QueueStatus::appointmentVariant($apt->status)])
                            </td>
                            <td>
                                <button type="button" class="text-sm font-semibold text-teal-600 hover:text-teal-700" data-bs-toggle="modal" data-bs-target="#appointmentStatusModal-{{ $apt->id }}">View</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-slate-500">No appointments scheduled yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">
            {{ $appointments->links() }}
        </div>
    </div>
</div>

@foreach ($appointments as $apt)
    <div class="modal fade" id="appointmentStatusModal-{{ $apt->id }}" tabindex="-1" aria-labelledby="appointmentStatusModalLabel-{{ $apt->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-2xl">
                <div class="modal-header border-b border-slate-200 px-5 py-4">
                    <div>
                        <h5 class="modal-title text-lg font-semibold text-slate-900" id="appointmentStatusModalLabel-{{ $apt->id }}">Appointment details</h5>
                        <p class="mt-1 text-sm text-slate-500">{{ $apt->appointment_number }} · {{ $apt->patient->full_name ?? '—' }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-5 py-5">
                    <div class="space-y-4 text-sm text-slate-700">
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div><span class="text-slate-500">Provider:</span> <span class="font-medium">{{ $apt->provider->full_name ?? '—' }}</span></div>
                            <div><span class="text-slate-500">Type:</span> <span class="font-medium">{{ $apt->appointmentType->name ?? '—' }}</span></div>
                            <div><span class="text-slate-500">Date/time:</span> <span class="font-medium">{{ $apt->starts_at->format('M d, Y g:i A') }}</span></div>
                            <div><span class="text-slate-500">Status:</span> <span class="ml-1">@include('partials.status-badge', ['label' => $apt->status, 'variant' => App\Support\QueueStatus::appointmentVariant($apt->status)])</span></div>
                        </div>
                        @if ($apt->reason)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Reason</div>
                                <div class="mt-2 text-slate-700">{{ $apt->reason }}</div>
                            </div>
                        @endif
                    </div>
                    <div class="mt-5 flex justify-end gap-3">
                        <a href="{{ route('appointments.show', $apt) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100">Open details</a>
                        <button type="button" class="inline-flex items-center justify-center rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-900" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-white shadow-2xl">
            <div class="modal-header border-b border-slate-200 bg-white px-5 py-4">
                <div>
                    <h5 class="modal-title text-lg font-semibold text-slate-900" id="appointmentModalLabel">Book Appointment</h5>
                    <p class="mt-1 text-sm text-slate-500">Select provider, time slot, and visit details</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-white px-5 py-5">
                @include('appointments.partials.booking-form')
            </div>
        </div>
    </div>
</div>
@endsection
