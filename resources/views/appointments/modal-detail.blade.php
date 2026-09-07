@php
    $appointment->load(['patient', 'provider', 'department', 'appointmentType', 'statusHistories.user', 'encounter', 'telehealthSession']);
@endphp

<div class="space-y-6">
    <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Appointment {{ $appointment->appointment_number }}</h1>
            <p class="mt-1 text-sm text-slate-500">Appointment and Scheduling System (ASS)</p>
        </div>
        <div class="flex flex-wrap justify-end gap-2">
            @can('create-appointments')
                @if ($appointment->status === 'CONFIRMED' || $appointment->status === 'PENDING')
                    <form class="appointment-action-form" data-appointment-action="check-in" method="POST" action="{{ route('appointments.check-in', $appointment) }}" onsubmit="return false;">
                        @csrf
                        <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Check In</button>
                    </form>
                @endif
            @endcan
            @can('cancel-appointments')
                @if ($appointment->status === 'CONFIRMED')
                    <form class="appointment-action-form" data-appointment-action="no-show" method="POST" action="{{ route('appointments.no-show', $appointment) }}" onsubmit="return false;">
                        @csrf
                        <button type="submit" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">Mark No-Show</button>
                    </form>
                @endif
                @if (in_array($appointment->status, ['PENDING', 'CONFIRMED', 'CHECKED-IN']))
                    <form class="appointment-action-form" data-appointment-action="cancel" method="POST" action="{{ route('appointments.cancel', $appointment) }}" onsubmit="return false;">
                        @csrf
                        <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Cancel</button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    <div id="appointment-modal-alert" class="hidden rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"></div>

    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <h3 class="mb-3 text-xs uppercase tracking-wider text-slate-500">Patient</h3>
                <p class="text-lg font-semibold">{{ $appointment->patient->full_name ?? '—' }}</p>
                <p class="font-mono text-sm text-slate-500">{{ $appointment->patient->mrn ?? '' }}</p>
            </div>
            <div>
                <h3 class="mb-3 text-xs uppercase tracking-wider text-slate-500">Provider</h3>
                <p class="text-lg font-semibold">{{ $appointment->provider->full_name ?? '—' }}</p>
                <p class="text-sm text-slate-500">{{ $appointment->department->name ?? '—' }}</p>
            </div>
            <div>
                <h3 class="mb-3 text-xs uppercase tracking-wider text-slate-500">Schedule</h3>
                <p class="text-sm text-slate-700">{{ $appointment->starts_at->format('l, F j, Y') }}</p>
                <p class="text-sm text-slate-700">{{ $appointment->starts_at->format('g:i A') }} — {{ $appointment->ends_at->format('g:i A') }}</p>
            </div>
            <div>
                <h3 class="mb-3 text-xs uppercase tracking-wider text-slate-500">Status</h3>
                <span class="rounded-full px-3 py-1 text-sm {{ $appointment->status === 'COMPLETED' ? 'bg-green-100 text-green-700' : ($appointment->status === 'CANCELLED' || $appointment->status === 'NO-SHOW' ? 'bg-red-100 text-red-700' : 'bg-teal-100 text-teal-700') }}">{{ $appointment->status }}</span>
                <p class="mt-2 text-sm text-slate-500">Type: {{ $appointment->appointmentType->name ?? '—' }}</p>
            </div>
        </div>
        @if ($appointment->reason)
            <div class="mt-4 rounded-lg bg-slate-50 p-3">
                <strong class="text-sm text-slate-700">Reason:</strong>
                <p class="mt-1 text-sm text-slate-600">{{ $appointment->reason }}</p>
            </div>
        @endif
    </div>

    @if ($appointment->telehealthSession)
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h3 class="mb-3 font-semibold text-slate-800">Telehealth Session</h3>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm text-slate-600">Status: <span class="font-medium">{{ $appointment->telehealthSession->status }}</span></p>
                    <p class="text-sm text-slate-600">Meeting ID: <span class="font-mono">{{ $appointment->telehealthSession->zoom_meeting_id ?? 'Not configured' }}</span></p>
                </div>
                <a href="{{ route('telehealth.show', $appointment->telehealthSession) }}" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">View Session</a>
            </div>
        </div>
    @endif

    @can('cancel-appointments')
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h3 class="mb-3 font-semibold text-slate-800">Reschedule Appointment</h3>
            <form class="appointment-action-form flex flex-wrap items-end gap-3" data-appointment-action="reschedule" method="POST" action="{{ route('appointments.reschedule', $appointment) }}" onsubmit="return false;">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">New Date &amp; Time</label>
                    <input type="datetime-local" name="starts_at" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Duration (min)</label>
                    <input type="number" name="duration" value="30" class="w-28 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">Reschedule</button>
            </form>
        </div>
    @endcan

    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <h3 class="mb-3 font-semibold text-slate-800">Status History</h3>
        <div class="space-y-2">
            @forelse ($appointment->statusHistories as $history)
                <div class="flex justify-between gap-3 border-b border-slate-100 py-1.5 text-sm">
                    <span>{{ $history->from_status ?? '—' }} → <strong>{{ $history->to_status }}</strong></span>
                    <span class="text-slate-500">{{ $history->created_at->format('M d, Y g:i A') }} · {{ $history->user->name ?? 'System' }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">No status changes recorded.</p>
            @endforelse
        </div>
    </div>
</div>
