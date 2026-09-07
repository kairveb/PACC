@extends('layouts.hims')

@section('title', 'Admissions')

@section('content')
<div class="space-y-6">
    <div class="panel-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Admissions</h2>
                <p class="text-sm text-slate-600">Inpatient and Bed Management System (IBMS)</p>
            </div>
            <div class="flex items-center gap-2">
                @if (request()->hasAny(['status']))
                    <a href="{{ route('admissions.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Clear filters</a>
                @endif
                <button type="button" class="rounded-2xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700" data-bs-toggle="modal" data-bs-target="#admissionModal">New Admission</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Number</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Patient</th>
                        <th class="relative py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <div class="flex items-center gap-2">
                                <span>Status</span>
                                <button type="button" data-filter-trigger data-filter-target="admissions-status-filter" aria-expanded="false" class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-700" aria-label="Filter admissions by status">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                        <path fill-rule="evenodd" d="M2.75 4.5A.75.75 0 0 1 3.5 3.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            <div id="admissions-status-filter" class="filter-panel hidden absolute left-0 top-full z-20 mt-2 w-64 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                                <form method="GET" class="space-y-3">
                                    <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                        <option value="">All statuses</option>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </th>
                        <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Bed</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Created</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($admissions as $adm)
                        @php
                            $activeAssignment = $adm->bedAssignments->where('status', 'ACTIVE')->first();
                            $bed = $activeAssignment?->bed;
                        @endphp
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="py-3 px-4 font-mono text-xs">{{ $adm->admission_number }}</td>
                            <td class="py-3 px-4 font-medium">{{ $adm->patient->full_name ?? '—' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $adm->status === 'DISCHARGED' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $adm->status === 'ADMITTED' || $adm->status === 'TRANSFERRED' ? 'bg-teal-100 text-teal-700' : '' }}
                                    {{ $adm->status === 'REQUESTED' ? 'bg-amber-100 text-amber-700' : '' }}
                                ">{{ $adm->status }}</span>
                            </td>
                            <td class="py-3 px-4">{{ $bed?->label ?? '—' }}</td>
                            <td class="py-3 px-4 text-slate-500 text-xs">{{ $adm->created_at->format('M d, Y') }}</td>
                            <td class="py-3 px-4">
                                <button type="button" class="text-teal-600 text-xs font-medium" data-bs-toggle="modal" data-bs-target="#admissionStatusModal-{{ $adm->id }}">Manage</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-400">No admissions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">{{ $admissions->links() }}</div>
    </div>
</div>

@foreach ($admissions as $adm)
    <div class="modal fade" id="admissionStatusModal-{{ $adm->id }}" tabindex="-1" aria-labelledby="admissionStatusModalLabel-{{ $adm->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-2xl">
                <div class="modal-header border-b border-slate-200 px-5 py-4">
                    <div>
                        <h5 class="modal-title text-lg font-semibold text-slate-900" id="admissionStatusModalLabel-{{ $adm->id }}">Admission status</h5>
                        <p class="mt-1 text-sm text-slate-500">{{ $adm->admission_number }} · {{ $adm->patient->full_name ?? '—' }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-5 py-5">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="text-sm text-slate-500">Current status:</span>
                        <span class="px-2 py-1 text-xs rounded-full
                            {{ $adm->status === 'DISCHARGED' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $adm->status === 'ADMITTED' || $adm->status === 'TRANSFERRED' ? 'bg-teal-100 text-teal-700' : '' }}
                            {{ $adm->status === 'REQUESTED' ? 'bg-amber-100 text-amber-700' : '' }}
                        ">{{ $adm->status }}</span>
                    </div>

                    @can('manage-admissions')
                        @if ($adm->status === 'REQUESTED')
                            <form method="POST" action="{{ route('admissions.approve', $adm) }}" class="space-y-4">
                                @csrf
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                                    Approve this admission request and continue the inpatient workflow.
                                </div>
                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">Approve</button>
                                </div>
                            </form>
                        @endif
                    @endcan

                    @can('manage-admissions')
                        @if (in_array($adm->status, ['ADMITTED', 'TRANSFERRED']))
                            <form method="POST" action="{{ route('admissions.discharge', $adm) }}" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-slate-700">Reason</label>
                                        <input type="text" name="reason" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-slate-700">Disposition</label>
                                        <input type="text" name="disposition" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-slate-700">Notes</label>
                                    <textarea name="notes" rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"></textarea>
                                </div>
                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700" onclick="return confirm('Discharge this patient and release the bed?')">Confirm discharge</button>
                                </div>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="admissionModal" tabindex="-1" aria-labelledby="admissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl">
            <div class="modal-header border-b border-slate-200 px-5 py-4">
                <div>
                    <h5 class="modal-title text-lg font-semibold text-slate-900" id="admissionModalLabel">New Admission</h5>
                    <p class="mt-1 text-sm text-slate-500">Inpatient and Bed Management System (IBMS)</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-5 py-5">
                <form method="POST" action="{{ route('admissions.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Patient *</label>
                        <select name="patient_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                            <option value="">Select patient</option>
                            @foreach (App\Models\Patient::orderBy('last_name')->get() as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->full_name }} — {{ $patient->mrn }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Attending Provider</label>
                        <select name="attending_provider_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                            <option value="">Select provider</option>
                            @foreach (App\Models\Provider::where('active', true)->get() as $provider)
                                <option value="{{ $provider->id }}">{{ $provider->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Reason for Admission</label>
                        <textarea name="reason" rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"></textarea>
                    </div>

                    <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                        <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700">Create Admission Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
                const isOpen = !!panel && !panel.classList.contains('hidden');

                closePanels();

                if (!isOpen && panel) {
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
@endsection
