@extends('layouts.hims')

@section('title', 'Join Telehealth Consultation')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="bg-teal-600 px-6 py-4">
            <h1 class="text-white font-bold text-lg">Telehealth Consultation</h1>
            <p class="text-teal-100 text-sm">{{ $session->appointment->appointment_number ?? '' }} · {{ $session->start_time?->format('M d, Y g:i A') }}</p>
        </div>

        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-slate-500 block">Patient</span>
                    <span class="font-medium">{{ $session->appointment->patient->full_name ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Provider</span>
                    <span class="font-medium">{{ $session->appointment->provider->full_name ?? '—' }}</span>
                </div>
            </div>

            @if ($session->zoom_meeting_id)
                <div class="rounded-lg bg-slate-50 p-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Meeting ID</span>
                        <span class="font-mono font-medium">{{ $session->zoom_meeting_id }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Status</span>
                        <span class="px-2 py-0.5 text-xs rounded-full bg-teal-100 text-teal-700 font-medium">{{ $session->status }}</span>
                    </div>
                </div>

                @if ($session->join_url)
                <div>
                    <a href="{{ $session->join_url }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Join Consultation
                    </a>
                </div>
                @endif
            @else
                <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                    <strong>Zoom meeting not configured.</strong>
                    <p class="mt-1">This telehealth session is recorded locally. Add Zoom credentials to enable a live video consultation.</p>
                </div>
            @endif

            <div class="text-xs text-slate-400 pt-2 border-t border-slate-100">
                For assistance, contact the hospital's telehealth support team.
            </div>
        </div>
    </div>
</div>
@endsection
