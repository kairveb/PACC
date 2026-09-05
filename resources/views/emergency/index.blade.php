@extends('layouts.hims')

@section('title', 'Emergency / ER')
@section('page-kicker', 'EERTS')
@section('page-title', 'Emergency & ER Triage')
@section('page-badge', 'ER triage')

@section('content')
<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-4">
        <div class="panel-card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-rose-600">L1</div>
                <span class="status-pill danger">Critical</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ $queue->where('priority', 'Level 1')->count() }}</div>
            <p class="mt-2 text-sm text-slate-500">Level 1 · Critical</p>
        </div>
        <div class="panel-card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-amber-600">L2</div>
                <span class="status-pill warning">Emergent</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ $queue->where('priority', 'Level 2')->count() }}</div>
            <p class="mt-2 text-sm text-slate-500">Level 2 · Emergent</p>
        </div>
        <div class="panel-card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-teal-600">L3</div>
                <span class="status-pill info">Queue</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ $queue->where('priority', 'Level 3')->count() }}</div>
            <p class="mt-2 text-sm text-slate-500">Level 3 · Prompt</p>
        </div>
        <div class="panel-card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-700">ER</div>
                <span class="status-pill success">Live</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ $queue->count() }}</div>
            <p class="mt-2 text-sm text-slate-500">Patients in queue</p>
        </div>
    </div>

    <div class="panel-card p-6">
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-rose-600">AI triage module</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-900">Fast ER triage intake</h2>
            </div>
            <button type="button" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700" data-bs-toggle="modal" data-bs-target="#triageModal">Open triage dashboard</button>
        </div>

        <div class="mb-6 flex justify-start">
            <button type="button" class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700" data-bs-toggle="modal" data-bs-target="#checkinLookupModal">Look up patient</button>
        </div>

        <form method="POST" action="{{ route('triage.store') }}" class="grid gap-5 lg:grid-cols-[1.4fr_0.6fr]">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="triage_patient_id" class="mb-1.5 block text-sm font-medium text-slate-700">Patient</label>
                    <select id="triage_patient_id" name="patient_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        <option value="">Select patient</option>
                        @foreach ($patients as $patient)
                            <option value="{{ $patient->id }}">{{ $patient->full_name }} — {{ $patient->mrn }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="triage_complaint" class="mb-1.5 block text-sm font-medium text-slate-700">Chief complaint</label>
                    <textarea id="triage_complaint" name="chief_complaint" rows="3" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100" placeholder="Difficulty breathing, chest pain, severe abdominal pain"></textarea>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="triage_symptoms" class="mb-1.5 block text-sm font-medium text-slate-700">Symptoms</label>
                        <input id="triage_symptoms" name="symptoms" type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100" placeholder="e.g. chest pain, fever, dizziness">
                    </div>
                    <div>
                        <label for="triage_pain_score" class="mb-1.5 block text-sm font-medium text-slate-700">Pain score</label>
                        <input id="triage_pain_score" name="pain_score" type="number" min="0" max="10" value="0" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    </div>
                    <div>
                        <label for="triage_heart_rate" class="mb-1.5 block text-sm font-medium text-slate-700">Heart rate</label>
                        <input id="triage_heart_rate" name="heart_rate" type="number" min="0" max="220" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100" placeholder="72">
                    </div>
                    <div>
                        <label for="triage_spo2" class="mb-1.5 block text-sm font-medium text-slate-700">SpO₂</label>
                        <input id="triage_spo2" name="spo2" type="number" min="0" max="100" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100" placeholder="98">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-slate-900">AI recommendation</h3>
                    <span class="rounded-full border border-slate-200 bg-white px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">Live</span>
                </div>
                <div id="triage_preview" class="min-h-[140px] rounded-xl border border-dashed border-slate-300 bg-white p-3 text-sm leading-6 text-slate-600">
                    Enter a complaint and vitals to generate a triage recommendation.
                </div>
                <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                    <button type="button" id="run_ai_triage" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700">Generate</button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">Save triage</button>
                </div>
            </div>
        </form>
    </div>

    <div class="panel-card overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Active ER queue</h2>
                <p class="text-sm text-slate-600">Review patient arrival, urgency, and waiting time at a glance.</p>
            </div>
            <button type="button" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700" data-bs-toggle="modal" data-bs-target="#intakeModal">New ER Intake</button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80">
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Patient</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Arrived</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Waiting</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Priority</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Complaint</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Status</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 text-right">Action</th>
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
                            <td class="px-5 py-4 text-right">
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
            <table class="min-w-full text-left">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80">
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Visit #</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Patient</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Arrived</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Status</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 text-right">Action</th>
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
                            <td class="px-5 py-4 text-right"><button type="button" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" data-bs-toggle="modal" data-bs-target="#visitStatusModal-{{ $visit->id }}">View</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-slate-400">No ER visits.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

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
                    <h5 class="modal-title text-lg font-semibold text-slate-900" id="triageModalLabel">Patient triage intake</h5>
                    <p class="mt-1 text-sm text-slate-500">Capture symptoms and recommended urgency</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-5 py-5">
                <form method="POST" action="{{ route('triage.store') }}" class="grid gap-6 xl:grid-cols-[1.5fr_0.8fr]">
                    @csrf

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
                                    <input name="pain_score" id="triage_modal_pain_score" type="number" min="0" max="10" value="0" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
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

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <button type="button" id="triage_modal_run_ai" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-200">Generate recommendation</button>
                                <button type="button" id="triage_modal_override" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-200">Clinical override</button>
                            </div>

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

        function syncTriageConfirmation(checked) {
            if (triageModalAiConfirmedField) triageModalAiConfirmedField.value = checked ? '1' : '0';
            if (triageModalAiConfirmedToggle) triageModalAiConfirmedToggle.checked = checked;
        }

        if (triageModalAiConfirmedToggle) {
            triageModalAiConfirmedToggle.addEventListener('change', function () {
                syncTriageConfirmation(this.checked);
            });
        }

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
@endpush
@endsection
