@extends('layouts.hims')

@section('title', 'Patients')
@section('page-kicker', 'Patient records')
@section('page-title', 'Patients')
@section('page-badge', 'Registry')

@section('content')
<div class="space-y-6">
    <div class="panel-card p-5">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by MRN, name, phone, email..." class="flex-1 min-w-[250px]">
            <input type="date" name="date_of_birth" value="{{ request('date_of_birth') }}">
            <select name="sex">
                <option value="">All sexes</option>
                <option value="Male" {{ request('sex') === 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ request('sex') === 'Female' ? 'selected' : '' }}>Female</option>
                <option value="Other" {{ request('sex') === 'Other' ? 'selected' : '' }}>Other</option>
            </select>
            <button type="submit" class="bg-slate-900">Search</button>
            <a href="{{ route('patients.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
        </form>
    </div>

    <div class="panel-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Patient directory</h2>
                <p class="text-sm text-slate-600">Search and manage patient records</p>
            </div>
            <button type="button" class="rounded-2xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700" data-bs-toggle="modal" data-bs-target="#registerPatientModal">Register Patient</button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="text-left">MRN</th>
                        <th class="text-left">Name</th>
                        <th class="text-left">Age/Sex</th>
                        <th class="text-left">Contact</th>
                        <th class="text-left">Status</th>
                        <th class="text-left">Registered</th>
                        <th class="text-left"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($patients as $patient)
                        <tr>
                            <td class="font-mono text-xs">{{ $patient->mrn }}</td>
                            <td class="font-medium text-slate-900">{{ $patient->full_name }}</td>
                            <td>{{ $patient->age }} / {{ $patient->sex }}</td>
                            <td class="text-slate-600">{{ $patient->phone ?? '—' }}</td>
                            <td>
                                <span class="status-pill {{ $patient->verified ? 'success' : 'warning' }}">{{ $patient->verified ? 'Verified' : 'Pending' }}</span>
                            </td>
                            <td class="text-slate-500">{{ $patient->created_at->format('M d, Y') }}</td>
                            <td>
                                <button type="button" class="text-sm font-semibold text-teal-600 hover:text-teal-700" data-bs-toggle="modal" data-bs-target="#patientOverviewModal-{{ $patient->id }}">View 360°</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">No patients found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">
            {{ $patients->links() }}
        </div>
    </div>

    @foreach ($patients as $patient)
        <div class="modal fade" id="patientOverviewModal-{{ $patient->id }}" tabindex="-1" aria-labelledby="patientOverviewModalLabel-{{ $patient->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-2xl">
                    <div class="modal-header border-b border-slate-200 px-5 py-4">
                        <div>
                            <h5 class="modal-title text-lg font-semibold text-slate-900" id="patientOverviewModalLabel-{{ $patient->id }}">Patient overview</h5>
                            <p class="mt-1 text-sm text-slate-500">{{ $patient->full_name ?? '—' }} · {{ $patient->mrn }}</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-5 py-5">
                        <div class="space-y-4 text-sm text-slate-700">
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <div><span class="text-slate-500">Age / sex:</span> <span class="font-medium">{{ $patient->age }} / {{ $patient->sex }}</span></div>
                                <div><span class="text-slate-500">Status:</span> <span class="status-pill {{ $patient->verified ? 'success' : 'warning' }} ml-1">{{ $patient->verified ? 'Verified' : 'Pending' }}</span></div>
                                <div><span class="text-slate-500">Phone:</span> <span class="font-medium">{{ $patient->phone ?? '—' }}</span></div>
                                <div><span class="text-slate-500">Email:</span> <span class="font-medium">{{ $patient->email ?? '—' }}</span></div>
                            </div>
                            @if ($patient->allergies)
                                <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-red-700">
                                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-red-600">Alerts</div>
                                    <div class="mt-1">{{ $patient->allergies }}</div>
                                </div>
                            @endif
                        </div>
                        <div class="mt-5 flex justify-end gap-3">
                            <a href="{{ route('patients.show', $patient) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100">Open details</a>
                            <button type="button" class="inline-flex items-center justify-center rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-900" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

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
                <div class="modal-body bg-white px-5 py-5">
                    @include('patients.partials.registration-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
