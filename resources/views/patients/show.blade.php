@extends('layouts.hims')

@section('title', 'Patient 360°')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Patient 360°</h1>
            <p class="text-sm text-slate-500 mt-1">Complete patient record</p>
        </div>
        <div class="flex gap-2">
            @can('verify-patients')
                @if (!$patient->verified)
                    <form method="POST" action="{{ route('patients.verify', $patient) }}">@csrf
                        <button class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">Verify Patient</button>
                    </form>
                @endif
            @endcan
            @can('view-patients')
                <a href="{{ route('patients.vitals', $patient) }}" class="px-4 py-2 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">View Vitals</a>
            @endcan
            @can('create-appointments')
                <a href="{{ route('appointments.index') }}" class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700">Book Appointment</a>
            @endcan
        </div>
    </div>

    {{-- Patient Header Card --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="flex flex-wrap gap-6 items-start">
            <div class="w-16 h-16 rounded-full bg-teal-600 text-white flex items-center justify-center text-2xl font-bold">
                {{ strtoupper(substr($patient->first_name, 0, 1)) }}{{ strtoupper(substr($patient->last_name, 0, 1)) }}
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-slate-800">{{ $patient->full_name }}</h2>
                    <span class="px-2 py-0.5 text-xs rounded-full {{ $patient->verified ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">{{ $patient->verified ? 'Verified' : 'Pending' }}</span>
                </div>
                <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    <div><span class="text-slate-500">MRN:</span> <span class="font-mono font-semibold">{{ $patient->mrn }}</span></div>
                    <div><span class="text-slate-500">DOB:</span> {{ $patient->date_of_birth?->format('M d, Y') }} ({{ $patient->age }} yrs)</div>
                    <div><span class="text-slate-500">Sex:</span> {{ $patient->sex }}</div>
                    <div><span class="text-slate-500">Contact:</span> {{ $patient->phone ?? '—' }}</div>
                    <div><span class="text-slate-500">Email:</span> {{ $patient->email ?? '—' }}</div>
                    <div><span class="text-slate-500">Civil Status:</span> {{ $patient->civil_status ?? '—' }}</div>
                    <div><span class="text-slate-500">Nationality:</span> {{ $patient->nationality ?? '—' }}</div>
                    <div><span class="text-slate-500">Registered:</span> {{ $patient->created_at->format('M d, Y') }}</div>
                </div>
                @if ($patient->allergies)
                    <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                        <strong>Allergies / Alerts:</strong> {{ $patient->allergies }}
                    </div>
                @endif
                @if ($contact = $patient->primaryEmergencyContact())
                    <div class="mt-3 p-3 bg-slate-50 rounded-lg text-sm">
                        <strong class="text-slate-700">Emergency Contact:</strong>
                        {{ $contact->name }} · {{ $contact->relationship }} · {{ $contact->phone }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex overflow-x-auto border-b border-slate-200" x-data="{ tab: 'overview' }">
            <button @click="tab='overview'" :class="tab==='overview' ? 'border-teal-600 text-teal-600' : 'border-transparent text-slate-500'" class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap hover:text-slate-700">Overview</button>
            <button @click="tab='appointments'" :class="tab==='appointments' ? 'border-teal-600 text-teal-600' : 'border-transparent text-slate-500'" class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap hover:text-slate-700">Appointments ({{ $patient->appointments->count() }})</button>
            <button @click="tab='encounters'" :class="tab==='encounters' ? 'border-teal-600 text-teal-600' : 'border-transparent text-slate-500'" class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap hover:text-slate-700">Encounters ({{ $patient->encounters->count() }})</button>
            <button @click="tab='er'" :class="tab==='er' ? 'border-teal-600 text-teal-600' : 'border-transparent text-slate-500'" class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap hover:text-slate-700">ER Visits ({{ $patient->erVisits->count() }})</button>
            <button @click="tab='admissions'" :class="tab==='admissions' ? 'border-teal-600 text-teal-600' : 'border-transparent text-slate-500'" class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap hover:text-slate-700">Admissions ({{ $patient->admissions->count() }})</button>
            <button @click="tab='documents'" :class="tab==='documents' ? 'border-teal-600 text-teal-600' : 'border-transparent text-slate-500'" class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap hover:text-slate-700">Documents</button>

            <div class="flex-1"></div>
        </div>

        <div class="p-6">
            {{-- Overview --}}
            <div x-show="tab==='overview'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php
                        $latestEncounter = $patient->encounters()->orderByDesc('started_at')->first();
                    @endphp

                    @if ($latestEncounter)
                        <div class="md:col-span-2 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <h3 class="font-medium text-indigo-800">Latest consultation summary</h3>
                                <a href="{{ route('encounters.show', $latestEncounter) }}" class="text-xs font-semibold text-indigo-700 hover:text-indigo-900">Open encounter</a>
                            </div>
                            <div class="mb-2 text-xs uppercase tracking-wide text-indigo-600">{{ $latestEncounter->type }} · {{ $latestEncounter->status }}</div>
                            <p class="text-sm text-slate-700"><span class="font-semibold text-slate-800">Assessment:</span> {{ $latestEncounter->assessment ?: 'No assessment recorded.' }}</p>
                            <p class="mt-2 text-sm text-slate-700"><span class="font-semibold text-slate-800">Plan:</span> {{ $latestEncounter->plan ?: 'No plan recorded.' }}</p>
                            @if ($latestEncounter->discharge_instructions)
                                <p class="mt-2 text-sm text-slate-700"><span class="font-semibold text-slate-800">Discharge instructions:</span> {{ $latestEncounter->discharge_instructions }}</p>
                            @endif
                            @if ($latestEncounter->follow_up_date)
                                <p class="mt-2 text-sm text-slate-700"><span class="font-semibold text-slate-800">Follow-up:</span> {{ $latestEncounter->follow_up_date->format('M d, Y') }}</p>
                            @endif
                        </div>
                    @endif

                    <div class="p-4 bg-slate-50 rounded-lg">
                        <h3 class="font-medium text-slate-700 mb-2">Address</h3>
                        @if ($addr = $patient->primaryAddress())
                            <p class="text-sm text-slate-600">{{ $addr->line1 }}, {{ $addr->city }}, {{ $addr->province }} {{ $addr->postal_code }}</p>
                        @else
                            <p class="text-sm text-slate-400">No address on file.</p>
                        @endif
                    </div>
                    <div class="p-4 bg-slate-50 rounded-lg">
                        <h3 class="font-medium text-slate-700 mb-2">Medical Alerts</h3>
                        <p class="text-sm text-slate-600">{{ $patient->allergies ?: 'None' }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-lg">
                        <h3 class="font-medium text-slate-700 mb-2">Upcoming Appointments</h3>
                        @forelse ($patient->appointments->whereIn('status', ['PENDING','CONFIRMED']) as $apt)
                            <p class="text-sm text-slate-600">{{ $apt->starts_at->format('M d, Y g:i A') }} — {{ $apt->provider->full_name ?? '—' }}</p>
                        @empty
                            <p class="text-sm text-slate-400">No upcoming appointments.</p>
                        @endforelse
                    </div>
                    <div class="p-4 bg-slate-50 rounded-lg">
                        <h3 class="font-medium text-slate-700 mb-2">Identifiers</h3>
                        @forelse ($patient->identifiers as $idf)
                            <p class="text-sm text-slate-600">{{ $idf->type }}: {{ $idf->value }}</p>
                        @empty
                            <p class="text-sm text-slate-400">No identifiers on file.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Appointments --}}
            <div x-show="tab==='appointments'">
                <table class="min-w-full text-sm">
                    <thead><tr class="text-left text-xs uppercase text-slate-500 border-b border-slate-200">
                        <th class="py-2 pr-4">Number</th><th class="py-2 pr-4">Provider</th><th class="py-2 pr-4">Date/Time</th><th class="py-2 pr-4">Type</th><th class="py-2">Status</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($patient->appointments as $apt)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-4 font-mono text-xs">{{ $apt->appointment_number }}</td>
                            <td class="py-2 pr-4">{{ $apt->provider->full_name ?? '—' }}</td>
                            <td class="py-2 pr-4">{{ $apt->starts_at->format('M d, Y g:i A') }}</td>
                            <td class="py-2 pr-4">{{ $apt->appointmentType->name ?? '—' }}</td>
                            <td class="py-2"><span class="px-2 py-1 text-xs rounded-full bg-slate-100">{{ $apt->status }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="py-4 text-center text-slate-400">No appointments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Encounters --}}
            <div x-show="tab==='encounters'">
                <table class="min-w-full text-sm">
                    <thead><tr class="text-left text-xs uppercase text-slate-500 border-b border-slate-200">
                        <th class="py-2 pr-4">Number</th><th class="py-2 pr-4">Type</th><th class="py-2 pr-4">Provider</th><th class="py-2 pr-4">Date</th><th class="py-2">Status</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($patient->encounters as $enc)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-4 font-mono text-xs">{{ $enc->encounter_number }}</td>
                            <td class="py-2 pr-4">{{ $enc->type }}</td>
                            <td class="py-2 pr-4">{{ $enc->provider->full_name ?? '—' }}</td>
                            <td class="py-2 pr-4">{{ $enc->started_at->format('M d, Y') }}</td>
                            <td class="py-2"><span class="px-2 py-1 text-xs rounded-full bg-slate-100">{{ $enc->status }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="py-4 text-center text-slate-400">No encounters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ER Visits --}}
            <div x-show="tab==='er'">
                @forelse ($patient->erVisits as $er)
                    <div class="mb-4 p-4 bg-slate-50 rounded-lg">
                        <div class="flex justify-between">
                            <div>
                                <span class="font-mono text-xs">{{ $er->visit_number }}</span>
                                <span class="ml-2 font-medium">{{ $er->arrived_at->format('M d, Y g:i A') }}</span>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full bg-slate-100">{{ $er->status }}</span>
                        </div>
                        <p class="text-sm text-slate-600 mt-2">{{ $er->chief_complaint }}</p>
                        @foreach ($er->triageAssessments as $ta)
                            <div class="mt-2 text-sm">
                                <span class="font-medium">Triage:</span> {{ $ta->priority }} · Pain {{ $ta->pain_score ?? '—' }}
                            </div>
                        @endforeach
                    </div>
                @empty
                    <p class="py-4 text-center text-slate-400">No ER visits.</p>
                @endforelse
            </div>

            {{-- Admissions / Bed History --}}
            <div x-show="tab==='admissions'">
                <table class="min-w-full text-sm">
                    <thead><tr class="text-left text-xs uppercase text-slate-500 border-b border-slate-200">
                        <th class="py-2 pr-4">Number</th><th class="py-2 pr-4">Status</th><th class="py-2 pr-4">Admitted</th><th class="py-2 pr-4">Bed</th><th class="py-2">Actions</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($patient->admissions as $adm)
                        @php
                            $activeAssignment = $adm->bedAssignments->where('status', 'ACTIVE')->first();
                            $bed = $activeAssignment?->bed;
                        @endphp
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-4 font-mono text-xs">{{ $adm->admission_number }}</td>
                            <td class="py-2 pr-4"><span class="px-2 py-1 text-xs rounded-full bg-slate-100">{{ $adm->status }}</span></td>
                            <td class="py-2 pr-4">{{ $adm->admitted_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="py-2 pr-4">{{ $bed?->label ?? '—' }}</td>
                            <td class="py-2"><button type="button" class="text-teal-600 text-xs font-medium" data-bs-toggle="modal" data-bs-target="#patientAdmissionStatusModal-{{ $adm->id }}">View</button></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="py-4 text-center text-slate-400">No admissions.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Documents --}}
            <div x-show="tab==='documents'">
                @forelse ($patient->clinicalDocuments as $doc)
                    <div class="p-3 bg-slate-50 rounded-lg mb-2 flex justify-between">
                        <span class="text-sm">{{ $doc->title ?? 'Document' }}</span>
                        <span class="text-xs text-slate-500">{{ $doc->created_at->format('M d, Y') }}</span>
                    </div>
                @empty
                    <p class="py-4 text-center text-slate-400">No documents on file.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@foreach ($patient->admissions as $adm)
    @php
        $activeAssignment = $adm->bedAssignments->where('status', 'ACTIVE')->first();
        $bed = $activeAssignment?->bed;
    @endphp
    <div class="modal fade" id="patientAdmissionStatusModal-{{ $adm->id }}" tabindex="-1" aria-labelledby="patientAdmissionStatusModalLabel-{{ $adm->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-2xl">
                <div class="modal-header border-b border-slate-200 px-5 py-4">
                    <div>
                        <h5 class="modal-title text-lg font-semibold text-slate-900" id="patientAdmissionStatusModalLabel-{{ $adm->id }}">Admission details</h5>
                        <p class="mt-1 text-sm text-slate-500">{{ $adm->admission_number }} · {{ $patient->full_name }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-5 py-5">
                    <div class="space-y-4 text-sm text-slate-700">
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div><span class="text-slate-500">Status:</span> <span class="ml-1 px-2 py-1 text-xs rounded-full bg-slate-100">{{ $adm->status }}</span></div>
                            <div><span class="text-slate-500">Bed:</span> <span class="font-medium">{{ $bed?->label ?? '—' }}</span></div>
                            <div><span class="text-slate-500">Admitted:</span> <span class="font-medium">{{ $adm->admitted_at?->format('M d, Y g:i A') ?? '—' }}</span></div>
                            <div><span class="text-slate-500">Reason:</span> <span class="font-medium">{{ $adm->reason ?? '—' }}</span></div>
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end gap-3">
                        <a href="{{ route('admissions.show', $adm) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100">Open details</a>
                        <button type="button" class="inline-flex items-center justify-center rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-900" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
