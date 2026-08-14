@extends('layouts.hims')

@section('title', 'Dashboard · Patient Access & Care Coordination')
@section('page-kicker', 'Command Center')
@section('page-title', 'Dashboard')
@section('page-description', 'A modern operating view for patient access and care coordination.')
@section('page-badge', 'Live workspace')

@section('content')
@php
    $user = auth()->user();
    $userRoles = $user?->roles->pluck('name')->toArray() ?? [];
    $isRegistration = $user?->hasRole('registration') ?? false;
    $isNurse = $user?->hasRole('nurse') ?? false;
    $isDoctor = $user?->hasRole('doctor') ?? false;
    $isPatient = $user?->hasRole('patient') ?? false;
    $isAdmin = $user?->hasAnyRole(['super-admin', 'hospital-admin']) ?? false;
    $primaryRole = $user?->roles()->first()?->name ?? 'guest';
    $userRole = $primaryRole;
@endphp

<div class="space-y-6">
    <section class="panel-card p-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-sky-600">Quick Start</p>
                <h3 class="mt-2 text-xl font-semibold text-slate-900">
                    @if ($isRegistration)
                        Front Desk Tasks
                    @elseif ($isNurse)
                        Triage & ER Workflow
                    @elseif ($isDoctor)
                        Clinical Review
                    @elseif ($isPatient)
                        My Care Overview
                    @elseif ($isAdmin)
                        Operations Overview
                    @else
                        Restricted Access
                    @endif
                </h3>
            </div>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @if ($isRegistration)
                <a href="{{ route('patients.create') }}" class="rounded-2xl border border-sky-200 bg-sky-50 p-4 transition hover:border-sky-400 hover:bg-sky-100">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-sky-600 text-white">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <div class="text-base font-semibold text-slate-900">Register Patient</div>
                    <div class="mt-1 text-sm text-slate-600">Create a new MRN and intake record</div>
                </a>

                <a href="{{ route('patients.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-200 text-slate-700">
                        <i class="bi bi-search"></i>
                    </div>
                    <div class="text-base font-semibold text-slate-900">Patient Lookup</div>
                    <div class="mt-1 text-sm text-slate-600">Find an existing patient quickly</div>
                </a>

                <a href="{{ route('appointments.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-200 text-slate-700">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <div class="text-base font-semibold text-slate-900">Appointments</div>
                    <div class="mt-1 text-sm text-slate-600">Review daily schedule and check-ins</div>
                </a>

                <a href="{{ route('emergency.index') }}" class="rounded-2xl border border-rose-200 bg-rose-50 p-4 transition hover:border-rose-400 hover:bg-rose-100">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-rose-600 text-white">
                        <i class="bi bi-hospital"></i>
                    </div>
                    <div class="text-base font-semibold text-slate-900">ER Queue</div>
                    <div class="mt-1 text-sm text-slate-600">Check emergency arrivals and priority levels</div>
                </a>
            @elseif ($userRole === 'nurse')
                <a href="{{ route('triage.create') }}" class="rounded-2xl border border-teal-200 bg-teal-50 p-4 transition hover:border-teal-400 hover:bg-teal-100">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-teal-600 text-white">
                        <i class="bi bi-heart-pulse"></i>
                    </div>
                    <div class="text-base font-semibold text-slate-900">New Triage</div>
                    <div class="mt-1 text-sm text-slate-600">Start patient urgency assessment</div>
                </a>

                <a href="{{ route('emergency.index') }}" class="rounded-2xl border border-amber-200 bg-amber-50 p-4 transition hover:border-amber-400 hover:bg-amber-100">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white">
                        <i class="bi bi-clipboard-pulse"></i>
                    </div>
                    <div class="text-base font-semibold text-slate-900">ER Queue</div>
                    <div class="mt-1 text-sm text-slate-600">Review waiting patients by severity</div>
                </a>

                <a href="{{ route('emergency.create') }}" class="rounded-2xl border border-rose-200 bg-rose-50 p-4 transition hover:border-rose-400 hover:bg-rose-100">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-rose-600 text-white">
                        <i class="bi bi-file-medical"></i>
                    </div>
                    <div class="text-base font-semibold text-slate-900">ER Intake</div>
                    <div class="mt-1 text-sm text-slate-600">Record arrivals and chief complaints</div>
                </a>

                <a href="{{ route('beds.index') }}" class="rounded-2xl border border-violet-200 bg-violet-50 p-4 transition hover:border-violet-400 hover:bg-violet-100">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-violet-600 text-white">
                        <i class="bi bi-bed"></i>
                    </div>
                    <div class="text-base font-semibold text-slate-900">Bed Board</div>
                    <div class="mt-1 text-sm text-slate-600">Track available and occupied beds</div>
                </a>
            @elseif ($userRole === 'doctor')
                <a href="{{ route('doctors.queue') }}" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 transition hover:border-emerald-400 hover:bg-emerald-100">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <div class="text-base font-semibold text-slate-900">Doctor Queue</div>
                    <div class="mt-1 text-sm text-slate-600">Review urgent patients waiting for review</div>
                </a>

                <a href="{{ route('encounters.index') }}" class="rounded-2xl border border-sky-200 bg-sky-50 p-4 transition hover:border-sky-400 hover:bg-sky-100">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-sky-600 text-white">
                        <i class="bi bi-journal-medical"></i>
                    </div>
                    <div class="text-base font-semibold text-slate-900">Encounters</div>
                    <div class="mt-1 text-sm text-slate-600">Open active patient consultations</div>
                </a>

                <a href="{{ route('appointments.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-200 text-slate-700">
                        <i class="bi bi-calendar2-week"></i>
                    </div>
                    <div class="text-base font-semibold text-slate-900">Today’s Schedule</div>
                    <div class="mt-1 text-sm text-slate-600">Check all booked visits for the day</div>
                </a>

                <a href="{{ route('patients.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-200 text-slate-700">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="text-base font-semibold text-slate-900">Patient List</div>
                    <div class="mt-1 text-sm text-slate-600">Jump straight to patient records</div>
                </a>
            @else
                <a href="{{ route('patients.create') }}" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-200 text-slate-700">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <div class="text-base font-semibold text-slate-900">Register Patient</div>
                </a>

                <a href="{{ route('emergency.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-200 text-slate-700">
                        <i class="bi bi-hospital"></i>
                    </div>
                    <div class="text-base font-semibold text-slate-900">ER Queue</div>
                </a>
            @endif
        </div>
    </section>

    @if (in_array($userRole, ['super-admin', 'hospital-admin']))
        <section class="panel-card p-6 lg:p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-600">Care coordination overview</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Operational snapshot for today</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Live data from registration, scheduling, telehealth, and bed management.</p>
                </div>
                <div class="metric-pill">Live data • Updated now</div>
            </div>
        </section>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @can('create-patients')
            <a href="{{ route('patients.create') }}" class="panel-card group flex flex-col items-start gap-3 p-5 transition hover:-translate-y-1 hover:border-teal-400">
                <div class="rounded-2xl bg-teal-50 p-3 text-teal-600"><i class="bi bi-person-plus-fill text-xl"></i></div>
                <div>
                    <div class="font-semibold text-slate-800">Register Patient</div>
                    <div class="text-xs text-slate-500 mt-0.5">New SPRS intake</div>
                </div>
                <span class="text-sm font-semibold text-teal-600 group-hover:translate-x-1 transition-transform">Open →</span>
            </a>
            @endcan
            @can('create-appointments')
            <a href="{{ route('appointments.create') }}" class="panel-card group flex flex-col items-start gap-3 p-5 transition hover:-translate-y-1 hover:border-sky-400">
                <div class="rounded-2xl bg-sky-50 p-3 text-sky-600"><i class="bi bi-calendar-plus text-xl"></i></div>
                <div>
                    <div class="font-semibold text-slate-800">Book Appointment</div>
                    <div class="text-xs text-slate-500 mt-0.5">Schedule a visit</div>
                </div>
                <span class="text-sm font-semibold text-sky-600 group-hover:translate-x-1 transition-transform">Open →</span>
            </a>
            @endcan
            @can('view-er')
            <a href="{{ route('emergency.create') }}" class="panel-card group flex flex-col items-start gap-3 p-5 transition hover:-translate-y-1 hover:border-rose-400">
                <div class="rounded-2xl bg-rose-50 p-3 text-rose-600"><i class="bi bi-hospital text-xl"></i></div>
                <div>
                    <div class="font-semibold text-slate-800">New ER Visit</div>
                    <div class="text-xs text-slate-500 mt-0.5">Emergency intake</div>
                </div>
                <span class="text-sm font-semibold text-rose-600 group-hover:translate-x-1 transition-transform">Open →</span>
            </a>
            @endcan
            @can('view-admissions')
            <a href="{{ route('admissions.create') }}" class="panel-card group flex flex-col items-start gap-3 p-5 transition hover:-translate-y-1 hover:border-violet-400">
                <div class="rounded-2xl bg-violet-50 p-3 text-violet-600"><i class="bi bi-box-arrow-in-right text-xl"></i></div>
                <div>
                    <div class="font-semibold text-slate-800">New Admission</div>
                    <div class="text-xs text-slate-500 mt-0.5">Request admission</div>
                </div>
                <span class="text-sm font-semibold text-violet-600 group-hover:translate-x-1 transition-transform">Open →</span>
            </a>
            @endcan
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="panel-card p-5">
                <div class="flex items-center justify-between">
                    <div class="rounded-2xl bg-teal-50 p-3 text-teal-600"><i class="bi bi-person-plus-fill text-xl"></i></div>
                    <span class="status-pill success">Active</span>
                </div>
                <div class="mt-6 text-3xl font-semibold text-slate-900">{{ $todayPatients }}</div>
                <div class="mt-1 text-sm text-slate-600">Patients registered today</div>
            </div>

            <div class="panel-card p-5">
                <div class="flex items-center justify-between">
                    <div class="rounded-2xl bg-sky-50 p-3 text-sky-600"><i class="bi bi-calendar-check-fill text-xl"></i></div>
                    <span class="status-pill info">Booked</span>
                </div>
                <div class="mt-6 text-3xl font-semibold text-slate-900">{{ $todayAppointments }}</div>
                <div class="mt-1 text-sm text-slate-600">Appointments scheduled</div>
            </div>

            <div class="panel-card p-5">
                <div class="flex items-center justify-between">
                    <div class="rounded-2xl bg-violet-50 p-3 text-violet-600"><i class="bi bi-camera-video-fill text-xl"></i></div>
                    <span class="status-pill warning">Live</span>
                </div>
                <div class="mt-6 text-3xl font-semibold text-slate-900">{{ $telehealthAppointments }}</div>
                <div class="mt-1 text-sm text-slate-600">Telehealth sessions</div>
            </div>

            <div class="panel-card p-5">
                <div class="flex items-center justify-between">
                    <div class="rounded-2xl bg-amber-50 p-3 text-amber-600"><i class="bi bi-hospital-fill text-xl"></i></div>
                    <span class="status-pill danger">In use</span>
                </div>
                <div class="mt-6 text-3xl font-semibold text-slate-900">{{ $occupiedBeds }}</div>
                <div class="mt-1 text-sm text-slate-600">Occupied beds</div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
            <div class="panel-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Today’s appointments</h3>
                        <p class="text-sm text-slate-600">Live schedule overview</p>
                    </div>
                    <a href="{{ route('appointments.index') }}" class="text-sm font-semibold text-teal-600 hover:text-teal-700">View all</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="text-left">Patient</th>
                                <th class="text-left">Doctor</th>
                                <th class="text-left">Time</th>
                                <th class="text-left">Type</th>
                                <th class="text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentAppointments as $apt)
                                <tr>
                                    <td>{{ $apt->patient->full_name ?? '—' }}</td>
                                    <td>{{ $apt->provider->full_name ?? '—' }}</td>
                                    <td>{{ $apt->starts_at->format('g:i A') }}</td>
                                    <td>{{ $apt->appointmentType->name ?? '—' }}</td>
                                    <td>@include('partials.status-badge', ['label' => $apt->status, 'variant' => App\Support\QueueStatus::appointmentVariant($apt->status)])</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-sm text-slate-500">No recent appointments.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-6">
                <div class="panel-card p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Bed occupancy</h3>
                            <p class="text-sm text-slate-600">Current ward status</p>
                        </div>
                        <a href="{{ route('beds.index') }}" class="text-sm font-semibold text-teal-600 hover:text-teal-700">Open board</a>
                    </div>
                    @php $bedTotal = $bedOccupancy->sum(); @endphp
                    <div class="space-y-3">
                        @foreach (['AVAILABLE' => 'Available', 'OCCUPIED' => 'Occupied', 'RESERVED' => 'Reserved', 'CLEANING' => 'Cleaning', 'MAINTENANCE' => 'Maintenance'] as $key => $label)
                            @php
                                $barClass = match ($key) {
                                    'AVAILABLE' => 'bg-teal-500',
                                    'OCCUPIED' => 'bg-rose-500',
                                    'RESERVED' => 'bg-amber-500',
                                    'CLEANING' => 'bg-sky-500',
                                    default => 'bg-slate-500',
                                };
                            @endphp
                            <div>
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <span class="text-slate-600">{{ $label }}</span>
                                    <span class="font-semibold text-slate-900">{{ $bedOccupancy[$key] ?? 0 }}</span>
                                </div>
                                <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $barClass }}" style="width: <?= e($bedTotal ? (($bedOccupancy[$key] ?? 0) / $bedTotal) * 100 : 0) ?>%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="panel-card p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">ER queue</h3>
                            <p class="text-sm text-slate-600">Priority patients waiting</p>
                        </div>
                        <a href="{{ route('emergency.index') }}" class="text-sm font-semibold text-teal-600 hover:text-teal-700">View ER</a>
                    </div>
                    <div class="space-y-3">
                        @forelse ($erQueue as $q)
                            @php
                                $dashboardStatusClass = App\Support\QueueStatus::variant($q->status ?? null);
                            @endphp
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                <div class="font-medium text-slate-900">{{ $q->erVisit->patient->full_name ?? '—' }}</div>
                                <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                                    <span class="font-medium text-slate-700">{{ $q->priority }}</span>
                                    @include('partials.status-badge', ['label' => $q->status, 'variant' => $dashboardStatusClass])
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-200 p-4 text-center text-sm text-slate-500">ER queue is empty.</div>
                        @endforelse
                    </div>
                </div>

                <div class="panel-card p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Follow-up due</h3>
                            <p class="text-sm text-slate-600">Patients needing a review</p>
                        </div>
                        <a href="{{ route('appointments.index', ['follow_up' => 'due']) }}" class="text-sm font-semibold text-teal-600 hover:text-teal-700">Open queue</a>
                    </div>
                    <div class="space-y-3">
                        @forelse ($followUpDue as $encounter)
                            <a href="{{ route('appointments.index', ['follow_up' => 'due']) }}" class="block rounded-2xl border border-slate-200 bg-slate-50 p-3 transition hover:border-teal-300 hover:bg-teal-50/40">
                                <div class="font-medium text-slate-900">{{ $encounter->patient->full_name ?? '—' }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ $encounter->follow_up_date?->format('M d, Y') }} · {{ $encounter->type }}</div>
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-200 p-4 text-center text-sm text-slate-500">No follow-up visits due soon.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @elseif ($userRole === 'doctor')
        <section class="panel-card border-l-4 border-emerald-500 p-5">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-emerald-600">Recommended next step</p>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900">Review the highest-priority patient and start the consult</h3>
                </div>
                <a href="{{ route('doctors.queue') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Open queue</a>
            </div>
        </section>

        <section class="panel-card p-6 lg:p-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-600">Clinical dashboard</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">My schedule</h2>
                </div>
                <div class="metric-pill">Today only</div>
            </div>
        </section>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="panel-card p-5">
                <div class="text-sm text-slate-500">Appointments today</div>
                <div class="mt-3 text-3xl font-semibold text-slate-900">{{ $myAppointments->count() }}</div>
            </div>
            <div class="panel-card p-5">
                <div class="text-sm text-slate-500">Patients seen</div>
                <div class="mt-3 text-3xl font-semibold text-slate-900">{{ $myPatientCount }}</div>
            </div>
            <div class="panel-card p-5">
                <div class="text-sm text-slate-500">Follow-up due</div>
                <div class="mt-3 text-3xl font-semibold text-slate-900">{{ $followUpDue->count() }}</div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="panel-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Today’s appointments</h3>
                    <a href="{{ route('appointments.index') }}" class="text-sm font-semibold text-teal-600">View schedule</a>
                </div>
                <div class="space-y-3">
                    @forelse ($myAppointments as $apt)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-medium text-slate-900">{{ $apt->patient->full_name ?? '—' }}</div>
                                    <div class="text-sm text-slate-600">{{ $apt->starts_at->format('g:i A') }} · {{ $apt->appointmentType->name ?? 'Visit' }}</div>
                                </div>
                                @include('partials.status-badge', ['label' => $apt->status, 'variant' => App\Support\QueueStatus::appointmentVariant($apt->status)])
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 p-4 text-sm text-slate-500">No appointments scheduled today.</div>
                    @endforelse
                </div>
            </div>

            <div class="panel-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Recent encounters</h3>
                    <a href="{{ route('encounters.index') }}" class="text-sm font-semibold text-teal-600">View</a>
                </div>
                <div class="space-y-3">
                    @forelse ($myEncounters as $enc)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <div class="font-medium text-slate-900">{{ $enc->patient->full_name ?? '—' }}</div>
                            <div class="mt-1 text-sm text-slate-600">{{ $enc->started_at?->format('M d, Y') }} · {{ $enc->type }}</div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 p-4 text-sm text-slate-500">No recent encounters.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @elseif ($userRole === 'nurse')
        <section class="panel-card border-l-4 border-teal-500 p-5">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-teal-600">Recommended next step</p>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900">Start triage for the next waiting patient</h3>
                </div>
                <a href="{{ route('triage.create') }}" class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Open triage</a>
            </div>
        </section>

        <section class="panel-card p-6 lg:p-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-600">Care floor</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Triage queue</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Monitor active triage and admissions from one view.</p>
                </div>
                <div class="metric-pill">Live</div>
            </div>
        </section>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="panel-card p-5">
                <div class="text-sm text-slate-500">Waiting triage</div>
                <div class="mt-3 text-3xl font-semibold text-slate-900">{{ $triageQueue->count() }}</div>
            </div>
            <div class="panel-card p-5">
                <div class="text-sm text-slate-500">Pending admissions</div>
                <div class="mt-3 text-3xl font-semibold text-slate-900">{{ $pendingAdmissions->count() }}</div>
            </div>
        </div>
    @elseif ($userRole === 'patient')
        <section class="panel-card p-6 lg:p-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-600">My care</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">My appointments</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Stay on top of upcoming visits and follow-up plans.</p>
                </div>
                <div class="metric-pill">Upcoming</div>
            </div>
        </section>

        <div class="panel-card p-6">
            <div class="space-y-3">
                @forelse ($myAppointments as $apt)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <div class="font-medium text-slate-900">{{ $apt->provider->full_name ?? '—' }}</div>
                        <div class="mt-1 text-sm text-slate-600">{{ $apt->starts_at->format('M d, Y g:i A') }} · {{ $apt->appointmentType->name ?? 'Visit' }}</div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-4 text-sm text-slate-500">No appointments scheduled.</div>
                @endforelse
            </div>
        </div>
    @elseif ($userRole === 'registration')
        <section class="panel-card p-6 lg:p-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-600">Front desk</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Registration desk</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Keep the flow moving for check-ins and scheduled visits.</p>
                </div>
                <div class="metric-pill">Today</div>
            </div>
        </section>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="panel-card p-5">
                <div class="text-sm text-slate-500">Scheduled today</div>
                <div class="mt-3 text-3xl font-semibold text-slate-900">{{ $registrationDeskQueue->count() }}</div>
            </div>
            <div class="panel-card p-5">
                <div class="text-sm text-slate-500">Checked in today</div>
                <div class="mt-3 text-3xl font-semibold text-slate-900">{{ $checkedInToday }}</div>
            </div>
        </div>
    @else
        <section class="panel-card border-l-4 border-slate-300 p-6 lg:p-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-500">Access</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">No dashboard modules assigned</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Your account is authenticated, but it does not have any HIMS dashboard permissions configured for this role. Contact an administrator to request access.</p>
                </div>
                <div class="metric-pill">Restricted</div>
            </div>
        </section>
@endif
</div>
@endsection
