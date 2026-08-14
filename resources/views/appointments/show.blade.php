@extends('layouts.hims')

@section('title', 'Appointment Detail')

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Appointment {{ $appointment->appointment_number }}</h1>
            <p class="text-sm text-slate-500 mt-1">Appointment and Scheduling System (ASS)</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            @can('create-appointments')
                @if ($appointment->status === 'CONFIRMED' || $appointment->status === 'PENDING')
                    <form method="POST" action="{{ route('appointments.check-in', $appointment) }}">@csrf
                        <button class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">Check In</button>
                    </form>
                @endif
            @endcan
            @can('cancel-appointments')
                @if ($appointment->status === 'CONFIRMED')
                    <form method="POST" action="{{ route('appointments.no-show', $appointment) }}">@csrf
                        <button class="px-4 py-2 text-sm bg-amber-600 text-white rounded-lg hover:bg-amber-700">Mark No-Show</button>
                    </form>
                @endif
                @if (in_array($appointment->status, ['PENDING', 'CONFIRMED', 'CHECKED-IN']))
                    <form method="POST" action="{{ route('appointments.cancel', $appointment) }}" onsubmit="return confirm('Cancel this appointment?')">@csrf
                        <button class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">Cancel</button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-xs uppercase tracking-wider text-slate-500 mb-3">Patient</h3>
                <p class="text-lg font-semibold">{{ $appointment->patient->full_name ?? '—' }}</p>
                <p class="text-sm text-slate-500 font-mono">{{ $appointment->patient->mrn ?? '' }}</p>
            </div>
            <div>
                <h3 class="text-xs uppercase tracking-wider text-slate-500 mb-3">Provider</h3>
                <p class="text-lg font-semibold">{{ $appointment->provider->full_name ?? '—' }}</p>
                <p class="text-sm text-slate-500">{{ $appointment->department->name ?? '—' }}</p>
            </div>
            <div>
                <h3 class="text-xs uppercase tracking-wider text-slate-500 mb-3">Schedule</h3>
                <p class="text-sm text-slate-700">{{ $appointment->starts_at->format('l, F j, Y') }}</p>
                <p class="text-sm text-slate-700">{{ $appointment->starts_at->format('g:i A') }} — {{ $appointment->ends_at->format('g:i A') }}</p>
            </div>
            <div>
                <h3 class="text-xs uppercase tracking-wider text-slate-500 mb-3">Status</h3>
                <span class="px-3 py-1 text-sm rounded-full {{ $appointment->status === 'COMPLETED' ? 'bg-green-100 text-green-700' : ($appointment->status === 'CANCELLED' || $appointment->status === 'NO-SHOW' ? 'bg-red-100 text-red-700' : 'bg-teal-100 text-teal-700') }}">{{ $appointment->status }}</span>
                <p class="text-sm text-slate-500 mt-2">Type: {{ $appointment->appointmentType->name ?? '—' }}</p>
            </div>
        </div>
        @if ($appointment->reason)
            <div class="mt-4 p-3 bg-slate-50 rounded-lg">
                <strong class="text-sm text-slate-700">Reason:</strong>
                <p class="text-sm text-slate-600 mt-1">{{ $appointment->reason }}</p>
            </div>
        @endif
    </div>

    @if ($appointment->telehealthSession)
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-800 mb-3">Telehealth Session</h3>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-600">Status: <span class="font-medium">{{ $appointment->telehealthSession->status }}</span></p>
                <p class="text-sm text-slate-600">Meeting ID: <span class="font-mono">{{ $appointment->telehealthSession->zoom_meeting_id ?? 'Not configured' }}</span></p>
            </div>
            <a href="{{ route('telehealth.show', $appointment->telehealthSession) }}" class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700">View Session</a>
        </div>
    </div>
    @endif

    @can('cancel-appointments')
        {{-- Reschedule --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-800 mb-3">Reschedule Appointment</h3>
            <form method="POST" action="{{ route('appointments.reschedule', $appointment) }}" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">New Date &amp; Time</label>
                    <input type="datetime-local" name="starts_at" required class="px-3 py-2 text-sm border border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Duration (min)</label>
                    <input type="number" name="duration" value="30" class="px-3 py-2 text-sm border border-slate-300 rounded-lg w-28">
                </div>
                <button type="submit" class="px-4 py-2 text-sm bg-slate-800 text-white rounded-lg hover:bg-slate-900">Reschedule</button>
            </form>
        </div>
    @endcan

    {{-- Status history --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-800 mb-3">Status History</h3>
        <div class="space-y-2">
            @forelse ($appointment->statusHistories as $history)
                <div class="flex justify-between text-sm py-1.5 border-b border-slate-100">
                    <span>{{ $history->from_status ?? '—' }} → <strong>{{ $history->to_status }}</strong></span>
                    <span class="text-slate-500">{{ $history->created_at->format('M d, Y g:i A') }} · {{ $history->user->name ?? 'System' }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">No status changes recorded.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
