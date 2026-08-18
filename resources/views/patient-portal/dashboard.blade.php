@extends('layouts.hims')

@section('title', 'Patient Portal')
@section('page-title', 'Patient Portal')
@section('page-kicker', 'Secure access')
@section('page-description', 'View your upcoming care visits, recent medical history, and telehealth appointments.')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
        <div class="card rounded-2xl border bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Welcome</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ $patient->full_name }}</h2>
                </div>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">MRN: {{ $patient->mrn }}</span>
            </div>

            <dl class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500">Date of birth</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $patient->date_of_birth?->format('M d, Y') ?? 'Not provided' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Phone</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $patient->phone ?? 'Not provided' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Email</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $patient->email ?? 'Not provided' }}</dd>
                </div>
            </dl>
        </div>

        <div class="card rounded-2xl border bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Upcoming appointments</h3>
                <a href="{{ route('patients.portal.appointments') }}" class="text-sm font-medium text-blue-600">View all</a>
            </div>

            @if ($upcomingAppointments->isEmpty())
                <p class="mt-4 text-sm text-slate-500">No upcoming appointments scheduled.</p>
            @else
                <div class="mt-5 space-y-3">
                    @foreach ($upcomingAppointments as $appointment)
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 p-3">
                            <div>
                                <p class="font-medium text-slate-900">{{ $appointment->appointmentType?->name ?? 'Consultation' }}</p>
                                <p class="text-sm text-slate-600">{{ $appointment->provider?->user?->name ?? 'Care team' }}</p>
                            </div>
                            <div class="text-right text-sm text-slate-600">
                                <p>{{ $appointment->starts_at?->format('M d, Y') }}</p>
                                <p>{{ $appointment->starts_at?->format('g:i A') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        @php
            $preArrivalProfile = $patient->preArrivalProfiles()->latest()->first();
        @endphp

        @if ($preArrivalProfile)
            <div class="card rounded-2xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-emerald-700">Pre-arrival ticket</p>
                        <h3 class="mt-2 text-lg font-semibold text-slate-900">Visit check-in ready</h3>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">{{ ucfirst($preArrivalProfile->status) }}</span>
                </div>

                <div class="mt-4 grid gap-4 md:grid-cols-[1fr_auto] md:items-center">
                    <div>
                        <p class="text-sm text-slate-700"><span class="font-semibold">Reason:</span> {{ $preArrivalProfile->visit_reason }}</p>
                        <p class="mt-2 text-xs text-slate-500">Secure token: {{ $preArrivalProfile->token }}</p>
                    </div>
                    <img src="{{ $preArrivalProfile->qr_code_url ?? 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($preArrivalProfile->token) }}" alt="Pre-arrival QR code" class="h-28 w-28 rounded-xl border border-emerald-200 bg-white p-2 shadow-sm">
                </div>
            </div>
        @endif

        <div class="card rounded-2xl border bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Upcoming telehealth</h3>
            @if ($upcomingTelehealth->isEmpty())
                <p class="mt-4 text-sm text-slate-500">No telehealth sessions scheduled.</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($upcomingTelehealth as $session)
                        <div class="rounded-xl border border-slate-200 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-medium text-slate-900">{{ $session->appointment?->provider?->user?->name ?? 'Care team' }}</p>
                                <span class="text-xs uppercase tracking-wide text-blue-600">{{ $session->status }}</span>
                            </div>
                            <p class="mt-2 text-sm text-slate-600">{{ $session->start_time?->format('M d, Y g:i A') }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card rounded-2xl border bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Recent medical history</h3>
            @if ($recentEncounters->isEmpty())
                <p class="mt-4 text-sm text-slate-500">No prior visits recorded.</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($recentEncounters as $encounter)
                        <div class="rounded-xl border border-slate-200 p-3">
                            <p class="font-medium text-slate-900">{{ $encounter->provider?->user?->name ?? 'Provider' }}</p>
                            <p class="text-sm text-slate-600">{{ $encounter->started_at?->format('M d, Y') }}</p>
                            <p class="mt-2 text-sm text-slate-700 line-clamp-3">{{ $encounter->chief_complaint ?? 'No chief complaint recorded.' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
