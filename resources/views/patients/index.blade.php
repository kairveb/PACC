@extends('layouts.hims')

@section('title', 'Patients')
@section('page-kicker', 'Patient records')
@section('page-title', 'Patients')
@section('page-badge', 'Registry')

@section('content')
<div class="space-y-6">
    <div class="panel-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Patient directory</h2>
                <p class="text-sm text-slate-600">Search and manage patient records</p>
            </div>
            <div class="flex items-center gap-2">
                @if (request()->hasAny(['q', 'date_of_birth', 'sex']))
                    <a href="{{ route('patients.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Clear filters</a>
                @endif
                <button type="button" class="rounded-2xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700" data-bs-toggle="modal" data-bs-target="#registerPatientModal">Register Patient</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="relative text-left align-top">
                            <div class="flex items-center gap-2">
                                <span>MRN</span>
                                <button type="button" data-filter-trigger data-filter-target="mrn-filter" aria-expanded="false" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-700" aria-label="Filter MRN">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                        <path fill-rule="evenodd" d="M2.75 4.5A.75.75 0 0 1 3.5 3.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            <div id="mrn-filter" class="filter-panel hidden absolute left-0 top-full z-20 mt-2 w-72 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                                <form method="GET" class="space-y-3">
                                    <input type="hidden" name="date_of_birth" value="{{ request('date_of_birth') }}">
                                    <input type="hidden" name="sex" value="{{ request('sex') }}">
                                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search MRN, name, phone, email..." class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </th>
                        <th class="relative text-left align-top">
                            <div class="flex items-center gap-2">
                                <span>Name</span>
                                <button type="button" data-filter-trigger data-filter-target="name-filter" aria-expanded="false" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-700" aria-label="Filter name">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                        <path fill-rule="evenodd" d="M2.75 4.5A.75.75 0 0 1 3.5 3.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            <div id="name-filter" class="filter-panel hidden absolute left-0 top-full z-20 mt-2 w-72 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                                <form method="GET" class="space-y-3">
                                    <input type="hidden" name="date_of_birth" value="{{ request('date_of_birth') }}">
                                    <input type="hidden" name="sex" value="{{ request('sex') }}">
                                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search patient name..." class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </th>
                        <th class="relative text-left align-top">
                            <div class="flex items-center gap-2">
                                <span>Age/Sex</span>
                                <button type="button" data-filter-trigger data-filter-target="sex-filter" aria-expanded="false" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-700" aria-label="Filter sex">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                        <path fill-rule="evenodd" d="M2.75 4.5A.75.75 0 0 1 3.5 3.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            <div id="sex-filter" class="filter-panel hidden absolute left-0 top-full z-20 mt-2 w-64 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                                <form method="GET" class="space-y-3">
                                    <input type="hidden" name="q" value="{{ request('q') }}">
                                    <input type="hidden" name="date_of_birth" value="{{ request('date_of_birth') }}">
                                    <select name="sex" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                        <option value="">All sexes</option>
                                        <option value="Male" {{ request('sex') === 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ request('sex') === 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Other" {{ request('sex') === 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </th>
                        <th class="relative text-left align-top">
                            <div class="flex items-center gap-2">
                                <span>Contact</span>
                                <button type="button" data-filter-trigger data-filter-target="contact-filter" aria-expanded="false" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-700" aria-label="Filter contact">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                        <path fill-rule="evenodd" d="M2.75 4.5A.75.75 0 0 1 3.5 3.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            <div id="contact-filter" class="filter-panel hidden absolute left-0 top-full z-20 mt-2 w-72 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                                <form method="GET" class="space-y-3">
                                    <input type="hidden" name="date_of_birth" value="{{ request('date_of_birth') }}">
                                    <input type="hidden" name="sex" value="{{ request('sex') }}">
                                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search phone or email..." class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </th>
                        <th class="text-left align-top">Status</th>
                        <th class="relative text-left align-top">
                            <div class="flex items-center gap-2">
                                <span>Registered</span>
                                <button type="button" data-filter-trigger data-filter-target="dob-filter" aria-expanded="false" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-700" aria-label="Filter DOB">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                        <path fill-rule="evenodd" d="M2.75 4.5A.75.75 0 0 1 3.5 3.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            <div id="dob-filter" class="filter-panel hidden absolute left-0 top-full z-20 mt-2 w-64 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                                <form method="GET" class="space-y-3">
                                    <input type="hidden" name="q" value="{{ request('q') }}">
                                    <input type="hidden" name="sex" value="{{ request('sex') }}">
                                    <input type="date" name="date_of_birth" value="{{ request('date_of_birth') }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </th>
                        <th class="text-left align-top"></th>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const triggers = document.querySelectorAll('[data-filter-trigger]');
            const panels = document.querySelectorAll('.filter-panel');

            const closePanels = () => {
                panels.forEach((panel) => panel.classList.add('hidden'));
                triggers.forEach((trigger) => trigger.setAttribute('aria-expanded', 'false'));
            };

            triggers.forEach((trigger) => {
                trigger.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const targetId = trigger.getAttribute('data-filter-target');
                    const panel = document.getElementById(targetId);
                    const isOpen = !panel.classList.contains('hidden');

                    closePanels();

                    if (!isOpen) {
                        panel.classList.remove('hidden');
                        trigger.setAttribute('aria-expanded', 'true');
                    }
                });
            });

            document.addEventListener('click', function (event) {
                if (!event.target.closest('[data-filter-trigger]') && !event.target.closest('.filter-panel')) {
                    closePanels();
                }
            });
        });
    </script>

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
