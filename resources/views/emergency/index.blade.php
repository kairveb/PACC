@extends('layouts.hims')

@section('title', 'Emergency / ER')
@section('page-kicker', 'EERTS')
@section('page-title', 'Emergency & ER Triage')
@section('page-badge', 'ER triage')

@section('content')
<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-4">
        <a href="{{ route('emergency.index', ['priority' => 'Level 1']) }}" class="panel-card block p-5 transition hover:-translate-y-0.5 hover:border-rose-200 hover:shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-rose-600">L1</div>
                <span class="status-pill danger">Critical</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ $queue->where('priority', 'Level 1')->count() }}</div>
            <p class="mt-2 text-sm text-slate-500">Level 1 · Critical</p>
        </a>
        <a href="{{ route('emergency.index', ['priority' => 'Level 2']) }}" class="panel-card block p-5 transition hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-amber-600">L2</div>
                <span class="status-pill warning">Emergent</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ $queue->where('priority', 'Level 2')->count() }}</div>
            <p class="mt-2 text-sm text-slate-500">Level 2 · Emergent</p>
        </a>
        <a href="{{ route('emergency.index', ['priority' => 'Level 3']) }}" class="panel-card block p-5 transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-teal-600">L3</div>
                <span class="status-pill info">Queue</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ $queue->where('priority', 'Level 3')->count() }}</div>
            <p class="mt-2 text-sm text-slate-500">Level 3 · Prompt</p>
        </a>
        <a href="{{ route('emergency.index', ['status' => 'live']) }}" class="panel-card block p-5 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-700">ER</div>
                <span class="status-pill success">Live</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ $queue->whereIn('status', [\App\Models\ErQueue::STATUS_WAITING, \App\Models\ErQueue::STATUS_IN_TREATMENT])->count() }}</div>
            <p class="mt-2 text-sm text-slate-500">Active queue</p>
        </a>
    </div>

    <div class="flex w-full flex-wrap items-center justify-between gap-4 pb-2">
        <button type="button" class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700" data-bs-toggle="modal" data-bs-target="#checkinLookupModal">Pre-arrival lookup</button>
        <div class="flex items-center gap-2">
            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">Recommended</span>
            <button type="button" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700" data-bs-toggle="modal" data-bs-target="#triageModal">Patient Triage Intake</button>
        </div>
    </div>

    <div class="panel-card overflow-hidden" id="active-er-queue">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Active ER queue</h2>
                <p class="text-sm text-slate-600">Review patient arrival, urgency, and waiting time at a glance.</p>
            </div>
            <div class="flex items-center gap-3">
                @if (request()->hasAny(['q', 'priority', 'status']))
                    <a href="{{ route('emergency.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Clear filters</a>
                @endif
                <div class="flex flex-col items-end gap-1 text-right">
                    <button type="button" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700" data-bs-toggle="modal" data-bs-target="#intakeModal">New ER Intake</button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-left">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80">
                        <th class="relative px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                            <div class="flex items-center gap-2">
                                <span>Patient</span>
                                <button type="button" data-filter-trigger data-filter-target="queue-patient-filter" aria-expanded="false" class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-700" aria-label="Filter patient">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                        <path fill-rule="evenodd" d="M2.75 4.5A.75.75 0 0 1 3.5 3.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            <div id="queue-patient-filter" class="filter-panel hidden absolute left-0 top-full z-20 mt-2 w-72 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                                <form method="GET" class="space-y-3">
                                    <input type="hidden" name="priority" value="{{ request('priority') }}">
                                    <input type="hidden" name="status" value="{{ request('status') }}">
                                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search patient or MRN..." class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Arrived</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Waiting</th>
                        <th class="relative px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                            <div class="flex items-center gap-2">
                                <span>Priority</span>
                                <button type="button" data-filter-trigger data-filter-target="queue-priority-filter" aria-expanded="false" class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-700" aria-label="Filter priority">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                        <path fill-rule="evenodd" d="M2.75 4.5A.75.75 0 0 1 3.5 3.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            <div id="queue-priority-filter" class="filter-panel hidden absolute left-0 top-full z-20 mt-2 w-64 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                                <form method="GET" class="space-y-3">
                                    <input type="hidden" name="q" value="{{ request('q') }}">
                                    <input type="hidden" name="status" value="{{ request('status') }}">
                                    <select name="priority" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                        <option value="">All priorities</option>
                                        @foreach (['Level 1', 'Level 2', 'Level 3', 'Level 4', 'Level 5'] as $priority)
                                            <option value="{{ $priority }}" {{ request('priority') === $priority ? 'selected' : '' }}>{{ $priority }}</option>
                                        @endforeach
                                    </select>
                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Complaint</th>
                        <th class="relative px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                            <div class="flex items-center gap-2">
                                <span>Status</span>
                                <button type="button" data-filter-trigger data-filter-target="queue-status-filter" aria-expanded="false" class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-700" aria-label="Filter status">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                        <path fill-rule="evenodd" d="M2.75 4.5A.75.75 0 0 1 3.5 3.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            <div id="queue-status-filter" class="filter-panel hidden absolute left-0 top-full z-20 mt-2 w-64 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                                <form method="GET" class="space-y-3">
                                    <input type="hidden" name="q" value="{{ request('q') }}">
                                    <input type="hidden" name="priority" value="{{ request('priority') }}">
                                    <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                        <option value="">All statuses</option>
                                        @foreach (['WAITING', 'IN_TREATMENT', 'DONE'] as $status)
                                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status === 'WAITING' ? 'Waiting' : ($status === 'IN_TREATMENT' ? 'In Treatment' : 'Done') }}</option>
                                        @endforeach
                                    </select>
                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </th>
                        <th class="w-[130px] min-w-[130px] px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($queue as $q)
                        @php
                            $queueStatusClass = App\Support\QueueStatus::variant($q->status ?? null);
                            $erPriorityVariant = match ($q->priority) {
                                'Level 1' => 'danger',
                                'Level 2', 'Level 3' => 'warning',
                                'Level 4' => 'info',
                                default => 'success',
                            };
                        @endphp
                        <tr class="border-b border-slate-200 align-top hover:bg-slate-50/50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900">{{ $q->erVisit->patient->full_name ?? '—' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $q->erVisit->patient->mrn ?? '—' }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $q->erVisit->arrived_at?->format('g:i A') ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $q->queued_at->diffInMinutes(now()) }} min</td>
                            <td class="px-5 py-4"><span class="status-pill {{ $erPriorityVariant }}">{{ $q->priority }}</span></td>
                            <td class="px-5 py-4">
                                <div class="max-w-md text-sm text-slate-700">{{ $q->erVisit->chief_complaint ?? 'No complaint recorded' }}</div>
                            </td>
                            <td class="px-5 py-4">@include('partials.status-badge', ['label' => $q->status, 'variant' => $queueStatusClass])</td>
                            <td class="w-[130px] min-w-[130px] px-5 py-4 text-right">
                                <button type="button" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700" data-bs-toggle="modal" data-bs-target="#queueStatusModal-{{ $q->id }}">Manage</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-slate-400">ER queue is empty.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 p-4">{{ $queue->links() }}</div>
    </div>

    <div class="panel-card overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Recent ER visits</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-left">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80">
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Visit #</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Patient</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Arrived</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Status</th>
                        <th class="w-[130px] min-w-[130px] px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visits as $visit)
                        @php
                            $visitStatusClass = App\Support\QueueStatus::variant($visit->status ?? null);
                        @endphp
                        <tr class="border-b border-slate-200 hover:bg-slate-50/50">
                            <td class="px-5 py-4 font-mono text-xs text-slate-600">{{ $visit->visit_number }}</td>
                            <td class="px-5 py-4 font-medium text-slate-900">{{ $visit->patient->full_name ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $visit->arrived_at?->format('M d, g:i A') ?? '—' }}</td>
                            <td class="px-5 py-4">@include('partials.status-badge', ['label' => $visit->status, 'variant' => $visitStatusClass])</td>
                            <td class="w-[130px] min-w-[130px] px-5 py-4 text-right"><button type="button" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" data-bs-toggle="modal" data-bs-target="#visitStatusModal-{{ $visit->id }}">View</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-slate-400">No ER visits.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        return;
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

@foreach ($queue as $q)
    <div class="modal fade" id="queueStatusModal-{{ $q->id }}" tabindex="-1" aria-labelledby="queueStatusModalLabel-{{ $q->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-2xl">
                <div class="modal-header border-b border-slate-200 px-5 py-4">
                    <div>
                        <h5 class="modal-title text-lg font-semibold text-slate-900" id="queueStatusModalLabel-{{ $q->id }}">ER queue status</h5>
                        <p class="mt-1 text-sm text-slate-500">{{ $q->erVisit->patient->full_name ?? '—' }} · {{ $q->erVisit->chief_complaint ?? '—' }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-5 py-5">
                    <form method="POST" action="{{ route('emergency.queue-status', $q) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                                <select name="status" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                    <option value="WAITING" {{ $q->status === 'WAITING' ? 'selected' : '' }}>Waiting</option>
                                    <option value="IN_TREATMENT" {{ $q->status === 'IN_TREATMENT' ? 'selected' : '' }}>In Treatment</option>
                                    <option value="DONE" {{ $q->status === 'DONE' ? 'selected' : '' }}>Done</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Provider</label>
                                <select name="provider_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                    <option value="">Provider</option>
                                    @foreach ($providers as $provider)
                                        <option value="{{ $provider->id }}" {{ $q->provider_id == $provider->id ? 'selected' : '' }}>{{ $provider->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">Update status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@foreach ($visits as $visit)
    <div class="modal fade" id="visitStatusModal-{{ $visit->id }}" tabindex="-1" aria-labelledby="visitStatusModalLabel-{{ $visit->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-2xl">
                <div class="modal-header border-b border-slate-200 px-5 py-4">
                    <div>
                        <h5 class="modal-title text-lg font-semibold text-slate-900" id="visitStatusModalLabel-{{ $visit->id }}">ER visit details</h5>
                        <p class="mt-1 text-sm text-slate-500">{{ $visit->visit_number }} · {{ $visit->patient->full_name ?? '—' }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-5 py-5">
                    <div class="grid grid-cols-1 gap-3 text-sm text-slate-700">
                        <div><span class="text-slate-500">Arrived:</span> <span class="font-medium">{{ $visit->arrived_at?->format('M d, Y g:i A') ?? '—' }}</span></div>
                        <div><span class="text-slate-500">Complaint:</span> <span class="font-medium">{{ $visit->chief_complaint ?? '—' }}</span></div>
                        <div><span class="text-slate-500">Status:</span> <span class="px-2 py-1 text-xs rounded-full bg-slate-100 ml-1">{{ $visit->status }}</span></div>
                        @if ($visit->queue)
                            <div><span class="text-slate-500">Priority:</span> <span class="font-medium">{{ $visit->queue->priority }}</span></div>
                            <div><span class="text-slate-500">Queue status:</span> <span class="font-medium">{{ $visit->queue->status }}</span></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="checkinLookupModal" tabindex="-1" aria-labelledby="checkinLookupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-2xl">
            <div class="modal-header border-b border-slate-200 px-5 py-4">
                <div>
                    <h5 class="modal-title text-lg font-semibold text-slate-900" id="checkinLookupModalLabel">Pre-arrival lookup</h5>
                    <p class="mt-1 text-sm text-slate-500">Find a patient by reference code</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-5 py-5">
                <form method="GET" action="{{ route('emergency.checkin.lookup') }}">
                    <div>
                        <label for="reference_code" class="mb-1.5 block text-sm font-medium text-slate-700">Pre-arrival reference code</label>
                        <input id="reference_code" name="reference_code" type="text" maxlength="12" placeholder="PAC-4829" class="w-full rounded-xl border border-sky-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100" required>
                    </div>
                    <div class="mt-5 flex justify-end gap-3">
                        <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">Look up patient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="intakeModal" tabindex="-1" aria-labelledby="intakeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl">
            <div class="modal-header border-b border-slate-200 px-5 py-4">
                <div>
                    <h5 class="modal-title text-lg font-semibold text-slate-900" id="intakeModalLabel">New ER Intake</h5>
                    <p class="mt-1 text-sm text-slate-500">AI triage + arrival registration</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-5 py-5">
                <form id="intakeForm" method="POST" action="{{ route('emergency.store') }}">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Patient</label>
                            <select name="patient_id" id="triagePatientId" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                <option value="">Select patient</option>
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->id }}">{{ $patient->full_name }} — {{ $patient->mrn }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Arrival date/time</label>
                            <input type="datetime-local" name="arrived_at" id="triageArrivedAt" value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Arrival method</label>
                            <select name="arrival_method" id="triageArrivalMethod" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                <option value="">Select</option>
                                <option value="Walk-in">Walk-in</option>
                                <option value="Ambulance">Ambulance</option>
                                <option value="Referral">Referral</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Chief complaint</label>
                            <textarea name="chief_complaint" id="triageComplaint" rows="3" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Referral details</label>
                            <textarea name="referral_details" id="triageReferral" rows="2" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Pain score (0-10)</label>
                            <input type="number" min="0" max="10" id="triagePain" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Observed symptoms</label>
                            <input type="text" id="triageSymptoms" placeholder="e.g. chest pain, shortness of breath" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>
                    </div>
                    <div class="mt-5">
                        <button type="button" id="runAiBtn" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700">Run AI triage</button>
                    </div>
                    <div id="aiResult" class="mt-4 hidden rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div id="aiResultBody" class="text-sm text-slate-700"></div>
                    </div>
                    <div class="mt-5 flex justify-end gap-3">
                        <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmQueueBtn" class="hidden inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">Confirm &amp; add to queue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="triageModal" tabindex="-1" aria-labelledby="triageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-2xl">
            <div class="modal-header border-b border-slate-200 px-5 py-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-rose-600">Step 1 of 3</p>
                    <h5 class="modal-title text-lg font-semibold text-slate-900" id="triageModalLabel">Patient triage intake</h5>
                    <p class="mt-1 text-sm text-slate-500">Capture symptoms and recommended urgency</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-5 py-5">
                <form method="POST" action="{{ route('triage.store') }}" class="grid gap-6 xl:grid-cols-[1.5fr_0.8fr]" id="triage-modal-form" novalidate>
                    @csrf
                    <div id="triage-modal-alert" class="hidden xl:col-span-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert"></div>

                    <div class="space-y-6">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="mb-5 flex items-center justify-between border-b border-slate-200 pb-4">
                                <h3 class="text-lg font-semibold text-slate-900">Patient intake</h3>
                                <span class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-teal-700">Required</span>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label for="triage_modal_patient_id" class="mb-1.5 block text-sm font-medium text-slate-700">Patient</label>
                                    <select name="patient_id" id="triage_modal_patient_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                        <option value="">Select patient</option>
                                        @foreach ($patients as $patient)
                                            <option value="{{ $patient->id }}">{{ $patient->full_name }} — {{ $patient->mrn }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="triage_modal_chief_complaint" class="mb-1.5 block text-sm font-medium text-slate-700">Chief complaint</label>
                                    <textarea name="chief_complaint" id="triage_modal_chief_complaint" rows="3" required placeholder="e.g. Difficulty breathing and chest pain" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"></textarea>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="triage_modal_symptoms" class="mb-1.5 block text-sm font-medium text-slate-700">Symptoms</label>
                                    <input name="symptoms" id="triage_modal_symptoms" type="text" placeholder="e.g. chest pain, shortness of breath, fever" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                </div>

                                <div>
                                    <label for="triage_modal_pain_score" class="mb-1.5 block text-sm font-medium text-slate-700">Pain score</label>
                                    <input name="pain_score" id="triage_modal_pain_score" type="number" min="0" max="10" step="1" value="0" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                </div>

                                <div>
                                    <label for="triage_modal_blood_pressure" class="mb-1.5 block text-sm font-medium text-slate-700">Blood pressure</label>
                                    <input name="blood_pressure" id="triage_modal_blood_pressure" type="text" placeholder="120/80" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                </div>

                                <div>
                                    <label for="triage_modal_heart_rate" class="mb-1.5 block text-sm font-medium text-slate-700">Heart rate</label>
                                    <input name="heart_rate" id="triage_modal_heart_rate" type="number" min="0" max="220" placeholder="72" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                </div>

                                <div>
                                    <label for="triage_modal_respiratory_rate" class="mb-1.5 block text-sm font-medium text-slate-700">Respiratory rate</label>
                                    <input name="respiratory_rate" id="triage_modal_respiratory_rate" type="number" min="0" max="80" placeholder="18" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                </div>

                                <div>
                                    <label for="triage_modal_temperature" class="mb-1.5 block text-sm font-medium text-slate-700">Temperature</label>
                                    <input name="temperature" id="triage_modal_temperature" type="number" step="0.1" min="30" max="45" placeholder="36.8" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                </div>

                                <div>
                                    <label for="triage_modal_spo2" class="mb-1.5 block text-sm font-medium text-slate-700">SpO₂</label>
                                    <input name="spo2" id="triage_modal_spo2" type="number" min="0" max="100" placeholder="98" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                </div>

                                <div class="md:col-span-2">
                                    <label for="triage_modal_notes" class="mb-1.5 block text-sm font-medium text-slate-700">Nurse notes</label>
                                    <textarea name="notes" id="triage_modal_notes" rows="3" placeholder="Optional situational notes" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <aside class="space-y-6">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-slate-900">Priority recommendation</h3>
                                <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">Live</span>
                            </div>

                            <div id="triage_modal_preview" class="rounded-2xl border border-dashed border-slate-300 bg-white p-4">
                                <div class="text-sm leading-6 text-slate-600">No assessment has been run yet. Complete the intake details and generate a priority recommendation.</div>
                            </div>

                            <div class="mt-4 grid gap-2 rounded-2xl border border-slate-200 bg-white p-3 text-sm text-slate-700">
                                <div class="flex items-center justify-between"><span class="font-medium text-slate-600">Severity score</span><strong id="triage_modal_severity_display">—</strong></div>
                                <div class="flex items-center justify-between"><span class="font-medium text-slate-600">Priority band</span><strong id="triage_modal_priority_display">—</strong></div>
                            </div>

                            @can('triage-patients')
                                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                    <button type="button" id="triage_modal_run_ai" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-200">Generate recommendation</button>
                                    <button type="button" id="triage_modal_override" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-200">Clinical override</button>
                                </div>
                            @endcan

                            <input type="hidden" name="ai_confirmed" id="triage_modal_ai_confirmed" value="0">
                            <input type="hidden" name="priority_override" id="triage_modal_priority_override" value="">

                            <label class="mt-4 flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3 text-sm text-slate-700">
                                <input type="checkbox" id="triage_modal_ai_confirmed_toggle" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span>I confirm the recommended priority, or I have applied a clinical override and documented the reason.</span>
                            </label>

                            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-end">
                                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-200">Save triage</button>
                            </div>
                        </div>
                    </aside>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const runAiBtn = document.getElementById('runAiBtn');
        const triageModalRunAi = document.getElementById('triage_modal_run_ai');

        const triageModalPreview = document.getElementById('triage_modal_preview');
        const triageModalSeverity = document.getElementById('triage_modal_severity_display');
        const triageModalPriority = document.getElementById('triage_modal_priority_display');
        const triageModalAiConfirmedToggle = document.getElementById('triage_modal_ai_confirmed_toggle');
        const triageModalAiConfirmedField = document.getElementById('triage_modal_ai_confirmed');
        const triageModalPriorityOverride = document.getElementById('triage_modal_priority_override');
        const triageModalOverride = document.getElementById('triage_modal_override');
        const triageModalForm = triageModalOverride?.closest('form');
        const triageModalSubmit = document.getElementById('triage-modal-form');

        const fieldErrorMessages = {
            patient_id: 'Please select a patient.',
            chief_complaint: 'Please enter the chief complaint.',
            pain_score: 'Pain score must be a whole number from 0 to 10.',
            ai_confirmed: 'Please confirm or override the recommended priority before saving.',
        };

        function clearTriageFieldErrors() {
            const alert = triageModalSubmit?.querySelector('#triage-modal-alert');
            if (alert) {
                alert.textContent = '';
                alert.classList.add('hidden');
            }
            triageModalSubmit?.querySelectorAll('.triage-field-error').forEach((error) => error.remove());
            triageModalSubmit?.querySelectorAll('[aria-invalid="true"]').forEach((field) => {
                field.removeAttribute('aria-invalid');
                field.classList.remove('border-rose-400');
            });
        }

        function showTriageFieldErrors(errors) {
            Object.entries(errors || {}).forEach(([fieldName, messages]) => {
                if (fieldName === '_form') {
                    const alert = triageModalSubmit?.querySelector('#triage-modal-alert');
                    if (alert) {
                        alert.textContent = Array.isArray(messages) ? messages[0] : messages;
                        alert.classList.remove('hidden');
                    }
                    return;
                }

                const field = triageModalSubmit?.querySelector(`[name="${fieldName}"]`);
                if (!field) return;

                field.setAttribute('aria-invalid', 'true');
                field.classList.add('border-rose-400');
                const message = document.createElement('p');
                message.className = 'triage-field-error mt-1.5 text-sm text-rose-700';
                message.textContent = Array.isArray(messages) ? messages[0] : messages;
                field.insertAdjacentElement('afterend', message);
            });
        }

        function validateTriageModal() {
            const errors = {};
            const patientId = triageModalSubmit?.querySelector('[name="patient_id"]')?.value;
            const complaint = triageModalSubmit?.querySelector('[name="chief_complaint"]')?.value?.trim();
            const painScore = triageModalSubmit?.querySelector('[name="pain_score"]')?.value?.trim();

            if (!patientId) errors.patient_id = [fieldErrorMessages.patient_id];
            if (!complaint) errors.chief_complaint = [fieldErrorMessages.chief_complaint];
            if (painScore !== '' && (!/^\d+$/.test(painScore) || Number(painScore) < 0 || Number(painScore) > 10)) {
                errors.pain_score = [fieldErrorMessages.pain_score];
            }

            return errors;
        }

        const overrideDialog = document.createElement('div');
        overrideDialog.className = 'fixed inset-0 hidden items-center justify-center bg-slate-900/40 p-4';
        overrideDialog.style.zIndex = '1080';
        overrideDialog.innerHTML = `
            <div class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-5 shadow-2xl">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-amber-600">Clinical safety</p>
                        <h3 class="mt-1 text-xl font-semibold text-slate-900">Clinical override</h3>
                    </div>
                    <button type="button" class="close-triage-override rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-600 hover:bg-slate-100">Close</button>
                </div>
                <div class="mt-5 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Override priority</label>
                        <select id="triage-override-level" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                            <option value="Emergency">Emergency — immediate escalation</option>
                            <option value="Urgent">Urgent — rapid review</option>
                            <option value="Prompt">Prompt — timely assessment</option>
                            <option value="Non-Urgent">Non-Urgent — standard review</option>
                            <option value="Routine">Routine — routine follow-up</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Override rationale</label>
                        <textarea id="triage-override-notes" rows="4" placeholder="Explain why the clinical team adjusted the recommendation." class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" class="cancel-triage-override inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancel</button>
                        <button type="button" class="apply-triage-override inline-flex items-center justify-center rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-400">Apply override</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(overrideDialog);

        function closeTriageOverride() {
            overrideDialog.classList.add('hidden');
            overrideDialog.classList.remove('flex');
        }

        function syncTriageConfirmation(checked) {
            if (triageModalAiConfirmedField) triageModalAiConfirmedField.value = checked ? '1' : '0';
            if (triageModalAiConfirmedToggle) triageModalAiConfirmedToggle.checked = checked;
        }

        if (triageModalAiConfirmedToggle) {
            triageModalAiConfirmedToggle.addEventListener('change', function () {
                syncTriageConfirmation(this.checked);
            });
        }

        triageModalSubmit?.addEventListener('submit', async function (event) {
            event.preventDefault();
            clearTriageFieldErrors();

            const clientErrors = validateTriageModal();
            if (Object.keys(clientErrors).length > 0) {
                showTriageFieldErrors(clientErrors);
                return;
            }

            const submitButton = triageModalSubmit.querySelector('button[type="submit"]');
            if (submitButton) submitButton.disabled = true;

            try {
                const response = await fetch(triageModalSubmit.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(triageModalSubmit),
                });

                const contentType = response.headers.get('content-type') || '';
                const responseText = await response.text();
                let payload = null;
                if (contentType.includes('application/json') && responseText) {
                    try {
                        payload = JSON.parse(responseText);
                    } catch (parseError) {
                        payload = null;
                    }
                }

                if (response.status === 422) {
                    showTriageFieldErrors(payload?.errors || {});
                    return;
                }

                if (!response.ok) {
                    throw new Error(payload?.message || `Unable to save triage (HTTP ${response.status}).`);
                }

                if (payload?.step === 2 && payload.html) {
                    window.dispatchEvent(new CustomEvent('hims:triage-step', { detail: payload }));
                } else {
                    window.location.assign(response.url);
                }
            } catch (error) {
                showTriageFieldErrors({ _form: [error.message || 'Unable to save triage.'] });
            } finally {
                if (submitButton) submitButton.disabled = false;
            }
        });

        if (triageModalOverride) {
            triageModalOverride.addEventListener('click', function () {
                overrideDialog.classList.remove('hidden');
                overrideDialog.classList.add('flex');
            });
        }

        overrideDialog.querySelector('.close-triage-override')?.addEventListener('click', closeTriageOverride);
        overrideDialog.querySelector('.cancel-triage-override')?.addEventListener('click', closeTriageOverride);
        overrideDialog.querySelector('.apply-triage-override')?.addEventListener('click', function () {
            const selectedPriority = overrideDialog.querySelector('#triage-override-level')?.value || 'Routine';
            const rationale = overrideDialog.querySelector('#triage-override-notes')?.value?.trim() || 'Clinical override applied after review.';
            const notesField = triageModalForm?.querySelector('[name="notes"]');

            if (notesField) {
                notesField.value = `Clinical override applied: ${selectedPriority}. Reason: ${rationale}`;
            }
            if (triageModalPriorityOverride) triageModalPriorityOverride.value = selectedPriority;
            syncTriageConfirmation(true);
            if (triageModalPriority) {
                triageModalPriority.textContent = selectedPriority;
                triageModalPriority.className = 'font-semibold ' + (selectedPriority === 'Emergency' ? 'text-rose-600' : selectedPriority === 'Urgent' ? 'text-amber-600' : 'text-emerald-600');
            }
            if (triageModalPreview) {
                triageModalPreview.innerHTML = `
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-amber-700">${selectedPriority}</span>
                        <span class="text-xs font-medium text-slate-500">Clinical override</span>
                    </div>
                    <p class="text-sm leading-6 text-slate-600">The clinical team adjusted the recommendation to ${selectedPriority} priority.</p>
                    <p class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-2 text-sm text-slate-700">${rationale}</p>
                `;
            }
            if (window.hisToast) hisToast('Clinical override saved to the triage notes.', 'success');
            closeTriageOverride();
        });

        async function requestTriageAssessment({ complaint, symptoms, painScore, vitals, previewEl, severityEl, priorityEl, successToastText }) {
            if (!complaint) {
                if (window.hisToast) hisToast('Enter the complaint before generating a recommendation.', 'warning');
                return;
            }

            try {
                const response = await window.HimsApi?.request?.('/api/v1/triage/score', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        chief_complaint: complaint,
                        pain_score: painScore,
                        symptoms,
                        vitals,
                    })
                });

                const data = response?.data || {};
                const score = Number(data.severity_score ?? 0);
                const band = data.priority_band || 'Green';
                const reasons = Array.isArray(data.reasons) ? data.reasons : [];

                if (severityEl) severityEl.textContent = `${score}/100`;
                if (priorityEl) {
                    priorityEl.textContent = band;
                    priorityEl.className = 'font-semibold ' + (band === 'Red' ? 'text-rose-600' : band === 'Yellow' ? 'text-amber-600' : 'text-emerald-600');
                }

                if (previewEl) {
                    previewEl.innerHTML = `
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <span class="inline-flex rounded-full ${band === 'Red' ? 'bg-rose-100 text-rose-700' : band === 'Yellow' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'} px-2.5 py-1 text-xs font-semibold uppercase tracking-[0.12em]">${band}</span>
                            <span class="text-xs font-medium text-slate-500">severity ${score}</span>
                        </div>
                        <ul class="list-disc space-y-1 pl-5 text-sm text-slate-600">
                            ${reasons.length ? reasons.map(reason => `<li>${reason}</li>`).join('') : '<li>No major risk indicators were detected.</li>'}
                        </ul>
                    `;
                }

                if (window.hisToast) hisToast(successToastText || 'AI triage recommendation generated.', 'success');
                syncTriageConfirmation(false);
                if (previewEl === document.getElementById('aiResultBody')) {
                    document.getElementById('confirmQueueBtn')?.classList.remove('hidden');
                }
            } catch (error) {
                if (window.hisToast) hisToast(error.message || 'Unable to generate triage recommendation.', 'danger');
            }
        }

        if (runAiBtn) {
            runAiBtn.addEventListener('click', async function () {
                const complaint = document.getElementById('triageComplaint')?.value?.trim() || '';
                const symptoms = (document.getElementById('triageSymptoms')?.value || '')
                    .split(',')
                    .map(value => value.trim())
                    .filter(Boolean);
                const painScore = Number(document.getElementById('triagePain')?.value || 0);
                const previewEl = document.getElementById('aiResultBody');
                const aiResult = document.getElementById('aiResult');

                if (previewEl) {
                    previewEl.innerHTML = '<div class="text-sm text-slate-600">Generating recommendation...</div>';
                    aiResult?.classList.remove('hidden');
                }

                await requestTriageAssessment({
                    complaint,
                    symptoms,
                    painScore,
                    vitals: {
                        spo2: document.getElementById('triageSpo2')?.value || null,
                        blood_pressure: document.getElementById('triageBloodPressure')?.value || null,
                        heart_rate: document.getElementById('triageHeartRate')?.value || null,
                        respiratory_rate: document.getElementById('triageRespiratoryRate')?.value || null,
                        temperature: document.getElementById('triageTemperature')?.value || null,
                    },
                    previewEl,
                    severityEl: null,
                    priorityEl: null,
                    successToastText: 'AI triage completed.',
                });
            });
        }

        document.getElementById('confirmQueueBtn')?.addEventListener('click', function () {
            document.getElementById('intakeForm')?.submit();
        });

        if (triageModalRunAi) {
            triageModalRunAi.addEventListener('click', async function () {
                const complaint = document.getElementById('triage_modal_chief_complaint')?.value?.trim() || '';
                const symptoms = (document.getElementById('triage_modal_symptoms')?.value || '')
                    .split(',')
                    .map(value => value.trim())
                    .filter(Boolean);
                const painScore = Number(document.getElementById('triage_modal_pain_score')?.value || 0);

                await requestTriageAssessment({
                    complaint,
                    symptoms,
                    painScore,
                    vitals: {
                        spo2: document.getElementById('triage_modal_spo2')?.value || null,
                        blood_pressure: document.getElementById('triage_modal_blood_pressure')?.value || null,
                        heart_rate: document.getElementById('triage_modal_heart_rate')?.value || null,
                        respiratory_rate: document.getElementById('triage_modal_respiratory_rate')?.value || null,
                        temperature: document.getElementById('triage_modal_temperature')?.value || null,
                    },
                    previewEl: triageModalPreview,
                    severityEl: triageModalSeverity,
                    priorityEl: triageModalPriority,
                    successToastText: 'AI triage completed.',
                });
            });
        }
    })();
</script>
<script>
    (function () {
        const modalEl = document.getElementById('triageModal');
        const modalBody = modalEl?.querySelector('.modal-body');
        const modalStep = modalEl?.querySelector('.modal-header p');
        const modalTitle = modalEl?.querySelector('.modal-title');
        const modalSubtitle = modalEl?.querySelector('.modal-header p + .modal-title + p');
        const queuePanel = document.getElementById('active-er-queue');
        let currentVisitId = null;

        if (!modalEl || !modalBody) return;

        function setModalStep(step) {
            const steps = {
                1: ['Step 1 of 3', 'Patient triage intake', 'Capture symptoms and recommended urgency'],
                2: ['Step 2 of 3', 'ER intake', 'Review arrival details and register the patient'],
                3: ['Step 3 of 3', 'Confirm priority to add to active queue', 'Review the clinical priority before queueing'],
            };
            const copy = steps[step];
            if (!copy) return;
            if (modalStep) modalStep.textContent = copy[0];
            if (modalTitle) modalTitle.textContent = copy[1];
            if (modalSubtitle) modalSubtitle.textContent = copy[2];
        }

        function clearChainErrors(form) {
            form.querySelectorAll('.triage-chain-error').forEach((error) => error.remove());
            form.querySelectorAll('[aria-invalid="true"]').forEach((field) => {
                field.removeAttribute('aria-invalid');
                field.classList.remove('border-rose-400');
            });
        }

        function showChainErrors(form, errors) {
            Object.entries(errors || {}).forEach(([fieldName, messages]) => {
                const field = form.querySelector(`[name="${fieldName}"]`);
                const message = Array.isArray(messages) ? messages[0] : messages;
                if (!field) {
                    const alert = document.createElement('div');
                    alert.className = 'triage-chain-error mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700';
                    alert.textContent = message;
                    form.prepend(alert);
                    return;
                }
                field.setAttribute('aria-invalid', 'true');
                field.classList.add('border-rose-400');
                const error = document.createElement('p');
                error.className = 'triage-chain-error mt-1.5 text-sm text-rose-700';
                error.textContent = message;
                field.insertAdjacentElement('afterend', error);
            });
        }

        async function parseResponse(response) {
            const text = await response.text();
            const contentType = response.headers.get('content-type') || '';
            let payload = null;
            if (contentType.includes('application/json') && text) {
                try { payload = JSON.parse(text); } catch (error) { payload = null; }
            }
            if (!response.ok) {
                const htmlTitle = text.match(/<title[^>]*>(.*?)<\/title>/is)?.[1]?.trim();
                const message = payload?.message || htmlTitle || text.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 240);
                const error = new Error(message || `Request failed with HTTP ${response.status}.`);
                error.status = response.status;
                error.validation = payload?.errors || null;
                throw error;
            }
            return payload || { success: true, html: text };
        }

        async function submitChainForm(form, onSuccess) {
            clearChainErrors(form);
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) submitButton.disabled = true;
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form),
                });
                const payload = await parseResponse(response);
                await onSuccess(payload);
            } catch (error) {
                if (error.validation) {
                    showChainErrors(form, error.validation);
                } else {
                    showChainErrors(form, { _form: [`HTTP ${error.status || 'error'}: ${error.message}`] });
                }
            } finally {
                if (submitButton) submitButton.disabled = false;
            }
        }

        function bindStep2() {
            const form = modalBody.querySelector('form[action$="/emergency"]');
            if (!form) return;
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                submitChainForm(form, async (payload) => {
                    currentVisitId = payload.visit_id;
                    setModalStep(3);
                    modalBody.innerHTML = payload.html;
                    bindStep3();
                });
            });
        }

        function bindStep3() {
            const form = modalBody.querySelector('form[action*="/emergency/"]');
            if (!form) return;
            const confirmationToggle = form.querySelector('#ai-confirmed-toggle');
            const confirmationField = form.querySelector('#ai_confirmed');

            const syncConfirmation = () => {
                if (confirmationField) {
                    confirmationField.value = confirmationToggle?.checked ? '1' : '0';
                }
            };

            confirmationToggle?.addEventListener('change', syncConfirmation);
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                syncConfirmation();
                submitChainForm(form, async () => {
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    if (queuePanel) {
                        const response = await fetch(window.location.href, { headers: { Accept: 'text/html' }, credentials: 'same-origin' });
                        const html = await response.text();
                        const nextQueuePanel = new DOMParser().parseFromString(html, 'text/html').querySelector('#active-er-queue');
                        if (nextQueuePanel) queuePanel.replaceWith(nextQueuePanel);
                    }
                });
            });
        }

        window.addEventListener('hims:triage-step', (event) => {
            const payload = event.detail || {};
            if (!payload.html) return;
            setModalStep(2);
            modalBody.innerHTML = payload.html;
            bindStep2();
        });
    })();
</script>
@endpush
@endsection
