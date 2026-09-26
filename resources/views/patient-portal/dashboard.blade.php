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

        <div class="card rounded-2xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.25em] text-emerald-700">Pre-registration</p>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900">Pre-register for your visit</h3>
                </div>
            </div>

            <p class="mt-3 text-sm text-slate-700">Share your visit details, medical history, and emergency contact before you arrive so check-in is faster and smoother.</p>

            @can('portal-dashboard')
                <button type="button" class="mt-5 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700" data-bs-toggle="modal" data-bs-target="#preRegistrationModal">
                    Pre-register for your visit
                </button>
            @endcan
        </div>

        <div class="card rounded-2xl border bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Upcoming appointments</h3>
                @can('portal-dashboard')
                    <a href="{{ route('patients.portal.appointments') }}" class="text-sm font-medium text-blue-600">View all</a>
                @endcan
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

                <div class="mt-5 rounded-2xl border border-emerald-300 bg-white p-4 text-center shadow-inner">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Reference code</p>
                    <div class="mt-3 text-3xl font-black tracking-[0.2em] text-emerald-700">{{ $preArrivalProfile->reference_code }}</div>
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
                        @php
                            $isLive = in_array($session->status, [\App\Models\TelehealthSession::STATUS_ACTIVE, \App\Models\TelehealthSession::STATUS_ONGOING], true);
                        @endphp
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-3">
                            <div>
                                <div class="flex items-center gap-3">
                                    <p class="font-medium text-slate-900">{{ $session->appointment?->provider?->user?->name ?? 'Care team' }}</p>
                                    <span class="text-xs uppercase tracking-wide text-blue-600">{{ $session->status }}</span>
                                </div>
                                <p class="mt-2 text-sm text-slate-600">{{ $session->start_time?->format('M d, Y g:i A') }}</p>
                            </div>
                            @if ($session->join_url)
                                <a href="{{ $session->join_url }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex shrink-0 items-center rounded-lg bg-teal-600 px-3 py-2 text-sm font-medium text-white hover:bg-teal-700 {{ $isLive ? '' : 'opacity-80' }}">
                                    {{ $isLive ? 'Join call' : 'View room' }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card rounded-2xl border bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-slate-900">My prescriptions</h3>
                <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-medium text-indigo-700">{{ $prescriptions->count() }}</span>
            </div>
            @if ($prescriptions->isEmpty())
                <p class="mt-4 text-sm text-slate-500">No prescriptions saved yet.</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($prescriptions as $prescription)
                        <div class="rounded-xl border border-slate-200 p-3">
                            <p class="font-medium text-slate-900">{{ $prescription->medication_name }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $prescription->dosage }}</p>
                            <p class="mt-2 text-sm text-slate-700">{{ $prescription->instructions }}</p>
                            <p class="mt-2 text-xs text-slate-500">Prescribed {{ $prescription->prescribed_at?->format('M d, Y g:i A') ?? 'recently' }}</p>
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

<div class="modal fade" id="preRegistrationModal" tabindex="-1" aria-labelledby="preRegistrationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-2xl">
            <div class="modal-header border-b border-slate-200 px-5 py-4">
                <div>
                    <h5 class="modal-title text-lg font-semibold text-slate-900" id="preRegistrationModalLabel">Pre-registration</h5>
                    <p class="mt-1 text-sm text-slate-500">Secure arrival details before your visit</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-5 py-5">
                <form method="POST" action="{{ route('portal.pre-register.store') }}" class="space-y-6">
                    @csrf

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-700">
                        This form is for your pre-arrival information only. Clinical or vital-sign fields are intentionally left blank and will be completed by staff during intake.
                    </div>

                    @include('portal.partials.pre-registration-fields', [
                        'patient' => $patient,
                        'address' => $patient->addresses->first(),
                        'contact' => $patient->emergencyContacts->first(),
                    ])

                    <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                        <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">Save pre-registration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
