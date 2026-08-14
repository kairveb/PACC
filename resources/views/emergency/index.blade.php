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
            <a href="{{ route('triage.create') }}" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700">Open triage dashboard</a>
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
                                <a href="{{ route('emergency.show', $q->erVisit) }}" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700">Manage</a>
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
                            <td class="px-5 py-4 text-right"><a href="{{ route('emergency.show', $visit) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-slate-400">No ER visits.</td></tr>
                    @endforelse
                </tbody>
            </table>
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

@push('scripts')
<script>
    (function () {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;

        async function apiRequest(url, method = 'GET', payload = null) {
            const headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
            if (token) headers['X-CSRF-TOKEN'] = token;
            if (payload !== null) {
                headers['Content-Type'] = 'application/json';
            }

            const response = await fetch(url, {
                method,
                credentials: 'same-origin',
                headers,
                body: payload ? JSON.stringify(payload) : undefined,
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || 'Request failed.');
            }

            return data;
        }

        const priorityMap = {
            1: 'Level 1',
            2: 'Level 2',
            3: 'Level 3',
            4: 'Level 4',
            5: 'Level 5',
        };

        const runAiBtn = document.getElementById('runAiBtn');
        const confirmQueueBtn = document.getElementById('confirmQueueBtn');
        const aiResult = document.getElementById('aiResult');
        const aiResultBody = document.getElementById('aiResultBody');

        if (runAiBtn) {
            runAiBtn.addEventListener('click', async function () {
                const complaint = document.getElementById('triageComplaint')?.value?.trim() || '';
                const painScore = Number(document.getElementById('triagePain')?.value || 0);
                const symptoms = (document.getElementById('triageSymptoms')?.value || '')
                    .split(',')
                    .map((value) => value.trim())
                    .filter(Boolean);

                if (!complaint) {
                    if (window.hisToast) hisToast('Enter the patient complaint before running triage.', 'warning');
                    return;
                }

                try {
                    const response = await apiRequest('/api/v1/triage/score', 'POST', {
                        chief_complaint: complaint,
                        pain_score: painScore,
                        symptoms,
                        vitals: {
                            spo2: 98,
                            blood_pressure: 120,
                            heart_rate: null,
                            respiratory_rate: null,
                            temperature: null,
                        }
                    });

                    const level = Number(response.data?.level || 3);
                    const reasons = response.data?.reasons || [];
                    const confidence = response.data?.confidence || 80;
                    const label = priorityMap[level] || 'Level 3';

                    aiResultBody.innerHTML = `
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-rose-700">${label}</span>
                            <span class="text-xs font-medium text-slate-500">confidence ${confidence}%</span>
                        </div>
                        <div class="space-y-2 text-sm text-slate-600">
                            ${reasons.length ? `<ul class="list-disc space-y-1 pl-5">${reasons.map((reason) => `<li>${reason}</li>`).join('')}</ul>` : '<p>No major risk indicators were detected.</p>'}
                        </div>
                    `;
                    aiResult.classList.remove('hidden');
                    aiResult.dataset.level = String(level);
                    confirmQueueBtn.classList.remove('hidden');
                } catch (error) {
                    if (window.hisToast) hisToast(error.message, 'danger');
                }
            });
        }

        if (confirmQueueBtn) {
            confirmQueueBtn.addEventListener('click', async function () {
                const patientId = document.getElementById('triagePatientId')?.value;
                const complaint = document.getElementById('triageComplaint')?.value?.trim();
                const arrivedAt = document.getElementById('triageArrivedAt')?.value || new Date().toISOString();
                const arrivalMethod = document.getElementById('triageArrivalMethod')?.value || 'Walk-in';
                const painScore = Number(document.getElementById('triagePain')?.value || 0);
                const level = Number(aiResult?.dataset?.level || 3);

                if (!patientId || !complaint) {
                    if (window.hisToast) hisToast('Select a patient and a complaint before adding to the ER queue.', 'warning');
                    return;
                }

                try {
                    const visitResponse = await apiRequest('/api/v1/emergency/visits', 'POST', {
                        patient_id: Number(patientId),
                        arrived_at: arrivedAt,
                        arrival_method: arrivalMethod,
                        chief_complaint: complaint,
                        referral_details: document.getElementById('triageReferral')?.value || null,
                    });

                    const visitId = visitResponse.data?.id;
                    if (!visitId) {
                        throw new Error('ER visit was not created.');
                    }

                    await apiRequest(`/api/v1/emergency/${visitId}/triage`, 'POST', {
                        chief_complaint: complaint,
                        pain_score: painScore,
                        priority: priorityMap[level] || 'Level 3',
                        notes: `AI triage recommended ${priorityMap[level] || 'Level 3'} based on complaint and symptoms.`,
                        treatment_area: 'ER Intake',
                        vitals: {
                            blood_pressure: '120/80',
                            heart_rate: null,
                            respiratory_rate: null,
                            temperature: null,
                            spo2: 98,
                        }
                    });

                    const modalEl = document.getElementById('intakeModal');
                    const modal = window.bootstrap ? window.bootstrap.Modal.getInstance(modalEl) : null;
                    if (modal) modal.hide();
                    document.getElementById('intakeForm')?.reset();
                    aiResult.classList.add('hidden');
                    confirmQueueBtn.classList.add('hidden');

                    if (window.hisToast) hisToast('Patient added to the ER queue successfully.', 'success');
                    window.location.reload();
                } catch (error) {
                    if (window.hisToast) hisToast(error.message, 'danger');
                }
            });
        }
    })();
</script>
@endpush
@endsection
