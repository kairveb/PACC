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
                @can('create-patients')
                    <button type="button" class="w-full rounded-2xl border border-sky-200 bg-sky-50 p-4 text-left transition hover:border-sky-400 hover:bg-sky-100" data-bs-toggle="modal" data-bs-target="#registerPatientModal">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-sky-600 text-white">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <div class="text-base font-semibold text-slate-900">Register Patient</div>
                        <div class="mt-1 text-sm text-slate-600">Create a new MRN and intake record</div>
                    </button>
                @endcan

                @can('view-patients')
                    <a href="{{ route('patients.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-200 text-slate-700">
                            <i class="bi bi-search"></i>
                        </div>
                        <div class="text-base font-semibold text-slate-900">Patient Lookup</div>
                        <div class="mt-1 text-sm text-slate-600">Find an existing patient quickly</div>
                    </a>
                @endcan

                @can('view-appointments')
                    <a href="{{ route('appointments.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-200 text-slate-700">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <div class="text-base font-semibold text-slate-900">Appointments</div>
                        <div class="mt-1 text-sm text-slate-600">Review daily schedule and check-ins</div>
                    </a>
                @endcan

                @can('view-er')
                    <a href="{{ route('emergency.index') }}" class="rounded-2xl border border-rose-200 bg-rose-50 p-4 transition hover:border-rose-400 hover:bg-rose-100">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-rose-600 text-white">
                            <i class="bi bi-hospital"></i>
                        </div>
                        <div class="text-base font-semibold text-slate-900">ER Queue</div>
                        <div class="mt-1 text-sm text-slate-600">Check emergency arrivals and priority levels</div>
                    </a>
                @endcan
            @elseif ($userRole === 'nurse')
                @can('triage-patients')
                    <a href="{{ route('emergency.index') }}" class="rounded-2xl border border-teal-200 bg-teal-50 p-4 transition hover:border-teal-400 hover:bg-teal-100">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-teal-600 text-white">
                            <i class="bi bi-heart-pulse"></i>
                        </div>
                        <div class="text-base font-semibold text-slate-900">Triage Board</div>
                        <div class="mt-1 text-sm text-slate-600">Open the ER triage intake workflow</div>
                    </a>
                @endcan

                @can('view-er')
                    <a href="{{ route('emergency.index') }}" class="rounded-2xl border border-amber-200 bg-amber-50 p-4 transition hover:border-amber-400 hover:bg-amber-100">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white">
                            <i class="bi bi-clipboard-pulse"></i>
                        </div>
                        <div class="text-base font-semibold text-slate-900">ER Queue</div>
                        <div class="mt-1 text-sm text-slate-600">Review waiting patients by severity</div>
                    </a>

                    <a href="{{ route('emergency.index') }}" class="rounded-2xl border border-rose-200 bg-rose-50 p-4 transition hover:border-rose-400 hover:bg-rose-100">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-rose-600 text-white">
                            <i class="bi bi-file-medical"></i>
                        </div>
                        <div class="text-base font-semibold text-slate-900">ER Intake</div>
                        <div class="mt-1 text-sm text-slate-600">Record arrivals and chief complaints</div>
                    </a>
                @endcan

                @can('view-beds')
                    <a href="{{ route('beds.index') }}" class="rounded-2xl border border-violet-200 bg-violet-50 p-4 transition hover:border-violet-400 hover:bg-violet-100">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-violet-600 text-white">
                            <i class="bi bi-bed"></i>
                        </div>
                        <div class="text-base font-semibold text-slate-900">Bed Board</div>
                        <div class="mt-1 text-sm text-slate-600">Track available and occupied beds</div>
                    </a>
                @endcan
            @elseif ($userRole === 'doctor')
                @can('view-encounters')
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
                @endcan

                @can('view-appointments')
                    <a href="{{ route('appointments.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-200 text-slate-700">
                            <i class="bi bi-calendar2-week"></i>
                        </div>
                        <div class="text-base font-semibold text-slate-900">Today’s Schedule</div>
                        <div class="mt-1 text-sm text-slate-600">Check all booked visits for the day</div>
                    </a>
                @endcan

                @can('view-patients')
                    <a href="{{ route('patients.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-200 text-slate-700">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="text-base font-semibold text-slate-900">Patient List</div>
                        <div class="mt-1 text-sm text-slate-600">Jump straight to patient records</div>
                    </a>
                @endcan
            @elseif ($isPatient)
                @if (auth()->user()->hasRole('patient'))
                    @can('portal-dashboard')
                        <button type="button" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-left transition hover:border-emerald-400 hover:bg-emerald-100" data-bs-toggle="modal" data-bs-target="#preRegistrationModal">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white">
                                <i class="bi bi-clipboard-check"></i>
                            </div>
                            <div class="text-base font-semibold text-slate-900">Pre-register for your visit</div>
                            <div class="mt-1 text-sm text-slate-600">Share your arrival details before you come in</div>
                        </button>

                        <a href="{{ route('patients.portal') }}" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-200 text-slate-700">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div class="text-base font-semibold text-slate-900">Patient Portal</div>
                            <div class="mt-1 text-sm text-slate-600">View appointments, history, and telehealth</div>
                        </a>
                    @endcan
                @endif
            @endif
        </div>
    </section>

    @if ($isPatient)
        @can('portal-dashboard')
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

                                <div class="grid gap-5 md:grid-cols-2">
                                    <div class="md:col-span-2">
                                        <h3 class="text-lg font-semibold text-slate-900">Patient details</h3>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Patient name</label>
                                        <input type="text" value="{{ $user->patient?->full_name ?? $user->name }}" disabled class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm text-slate-700">
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Age</label>
                                        <input type="text" value="{{ $user->patient?->age ?? 0 }} years" disabled class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm text-slate-700">
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Phone</label>
                                        <input type="tel" name="contact_phone" value="{{ old('contact_phone', $user->patient?->phone) }}" data-phone-input class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" inputmode="numeric" pattern="^(09\d{9}|\+639\d{9})$" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                                        <input type="email" name="contact_email" value="{{ old('contact_email', $user->patient?->email) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Home address</label>
                                        <input type="text" name="address_line1" value="{{ old('address_line1', $user->patient?->primaryAddress()?->line1 ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="House number, street, subdivision">
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">City</label>
                                        <input type="text" name="address_city" value="{{ old('address_city', $user->patient?->primaryAddress()?->city ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Province</label>
                                        <input type="text" name="address_province" value="{{ old('address_province', $user->patient?->primaryAddress()?->province ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Postal code</label>
                                        <input type="text" name="address_postal_code" value="{{ old('address_postal_code', $user->patient?->primaryAddress()?->postal_code ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Visit reason</label>
                                        <textarea name="visit_reason" rows="3" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="Briefly describe why you are coming in today.">{{ old('visit_reason') }}</textarea>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Initial notes</label>
                                        <textarea name="initial_notes" rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="Add any context the intake team should know before arrival.">{{ old('initial_notes') }}</textarea>
                                    </div>

                                    <div class="md:col-span-2">
                                        <h3 class="text-lg font-semibold text-slate-900">Medical history</h3>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Medical history</label>
                                        <textarea name="medical_history" rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="Asthma, hypertension, previous surgeries, chronic conditions...">{{ old('medical_history') }}</textarea>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Current medications</label>
                                        <textarea name="current_medications" rows="2" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="List medications currently being taken.">{{ old('current_medications') }}</textarea>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Allergies</label>
                                        <textarea name="allergies" rows="2" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="Penicillin, peanuts, latex...">{{ old('allergies') }}</textarea>
                                    </div>

                                    <div class="md:col-span-2">
                                        <h3 class="text-lg font-semibold text-slate-900">Emergency contact</h3>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Contact name</label>
                                        <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $user->patient?->emergencyContacts->first()?->name ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Relationship</label>
                                        <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $user->patient?->emergencyContacts->first()?->relationship ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Emergency contact phone</label>
                                        <input type="tel" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $user->patient?->emergencyContacts->first()?->phone ?? '') }}" data-phone-input class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" inputmode="numeric" pattern="^(09\d{9}|\+639\d{9})$" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
                                    </div>
                                </div>

                                <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">Save pre-registration</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    @endif

    @if (! $isPatient)
        <div class="modal fade" id="registerPatientModal" tabindex="-1" aria-labelledby="registerPatientModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-2xl">
                    <div class="modal-header border-b border-slate-200 px-5 py-4">
                        <div>
                            <h5 class="modal-title text-lg font-semibold text-slate-900" id="registerPatientModalLabel">Register Patient</h5>
                            <p class="mt-1 text-sm text-slate-500">Patient demographics and contact info</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-5 py-5">
                        @include('patients.partials.registration-form')
                    </div>
                </div>
            </div>
        </div>
    @endif

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
            <button type="button" class="panel-card group flex w-full flex-col items-start gap-3 p-5 text-left transition hover:-translate-y-1 hover:border-teal-400" data-bs-toggle="modal" data-bs-target="#registerPatientModal">
                <div class="rounded-2xl bg-teal-50 p-3 text-teal-600"><i class="bi bi-person-plus-fill text-xl"></i></div>
                <div>
                    <div class="font-semibold text-slate-800">Register Patient</div>
                    <div class="text-xs text-slate-500 mt-0.5">New SPRS intake</div>
                </div>
                <span class="text-sm font-semibold text-teal-600 group-hover:translate-x-1 transition-transform">Open →</span>
            </button>
            @endcan
            @can('create-appointments')
            <a href="{{ route('appointments.index') }}" class="panel-card group flex flex-col items-start gap-3 p-5 transition hover:-translate-y-1 hover:border-sky-400">
                <div class="rounded-2xl bg-sky-50 p-3 text-sky-600"><i class="bi bi-calendar-plus text-xl"></i></div>
                <div>
                    <div class="font-semibold text-slate-800">Appointments</div>
                    <div class="text-xs text-slate-500 mt-0.5">Review and schedule visits</div>
                </div>
                <span class="text-sm font-semibold text-sky-600 group-hover:translate-x-1 transition-transform">Open →</span>
            </a>
            @endcan
            @can('view-er')
            <a href="{{ route('emergency.index') }}" class="panel-card group flex flex-col items-start gap-3 p-5 transition hover:-translate-y-1 hover:border-rose-400">
                <div class="rounded-2xl bg-rose-50 p-3 text-rose-600"><i class="bi bi-hospital text-xl"></i></div>
                <div>
                    <div class="font-semibold text-slate-800">ER queue</div>
                    <div class="text-xs text-slate-500 mt-0.5">Emergency intake</div>
                </div>
                <span class="text-sm font-semibold text-rose-600 group-hover:translate-x-1 transition-transform">Open →</span>
            </a>
            @endcan
            @can('view-admissions')
            <a href="{{ route('admissions.index') }}" class="panel-card group flex flex-col items-start gap-3 p-5 transition hover:-translate-y-1 hover:border-violet-400">
                <div class="rounded-2xl bg-violet-50 p-3 text-violet-600"><i class="bi bi-box-arrow-in-right text-xl"></i></div>
                <div>
                    <div class="font-semibold text-slate-800">Admissions</div>
                    <div class="text-xs text-slate-500 mt-0.5">Review inpatient requests</div>
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
                <a href="{{ route('emergency.index') }}" class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Open triage</a>
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
