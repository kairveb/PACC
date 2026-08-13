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
            <div class="mt-4 text-3xl font-semibold text-slate-900">—</div>
            <p class="mt-2 text-sm text-slate-500">Level 1 · Critical</p>
        </div>
        <div class="panel-card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-amber-600">L2</div>
                <span class="status-pill warning">Emergent</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">—</div>
            <p class="mt-2 text-sm text-slate-500">Level 2 · Emergent</p>
        </div>
        <div class="panel-card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-teal-600">L3</div>
                <span class="status-pill info">Queue</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">—</div>
            <p class="mt-2 text-sm text-slate-500">Avg door-to-triage</p>
        </div>
        <div class="panel-card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-700">ER</div>
                <span class="status-pill success">Live</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">—</div>
            <p class="mt-2 text-sm text-slate-500">ER bays occupied</p>
        </div>
    </div>

    <div class="panel-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Active ER queue</h2>
                <p class="text-sm text-slate-600">Use the Assign Bed action to move a patient into a bed after triage.</p>
            </div>
            <button type="button" class="rounded-2xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700" data-bs-toggle="modal" data-bs-target="#intakeModal">New ER Intake</button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="text-left">Patient</th>
                        <th class="text-left">Arrived</th>
                        <th class="text-left">Waiting</th>
                        <th class="text-left">Priority</th>
                        <th class="text-left">Complaint</th>
                        <th class="text-left">Status</th>
                        <th class="text-left"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($queue as $q)
                        @php
                            $queueStatusClass = App\Support\QueueStatus::variant($q->status ?? null);
                        @endphp
                        <tr>
                            <td class="font-medium text-slate-900">{{ $q->erVisit->patient->full_name ?? '—' }}</td>
                            <td>{{ $q->erVisit->arrived_at->format('g:i A') }}</td>
                            <td>{{ $q->queued_at->diffInMinutes(now()) }} min</td>
                            <td>
                                @php
                                    $erPriorityVariant = match ($q->priority) {
                                        'Level 1' => 'danger',
                                        'Level 2', 'Level 3' => 'warning',
                                        'Level 4' => 'info',
                                        default => 'success',
                                    };
                                @endphp
                                <span class="status-pill {{ $erPriorityVariant }}">{{ $q->priority }}</span>
                            </td>
                            <td class="max-w-xs truncate">{{ $q->erVisit->chief_complaint }}</td>
                            <td>@include('partials.status-badge', ['label' => $q->status, 'variant' => $queueStatusClass])</td>
                            <td>
                                <a href="{{ route('emergency.show', $q->erVisit) }}" class="text-sm font-semibold text-teal-600 hover:text-teal-700">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">ER queue is empty.</td></tr>
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
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="text-left">Visit #</th>
                        <th class="text-left">Patient</th>
                        <th class="text-left">Arrived</th>
                        <th class="text-left">Status</th>
                        <th class="text-left"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visits as $visit)
                        @php
                            $visitStatusClass = App\Support\QueueStatus::variant($visit->status ?? null);
                        @endphp
                        <tr>
                            <td class="font-mono text-xs">{{ $visit->visit_number }}</td>
                            <td class="font-medium text-slate-900">{{ $visit->patient->full_name ?? '—' }}</td>
                            <td>{{ $visit->arrived_at->format('M d, g:i A') }}</td>
                            <td>@include('partials.status-badge', ['label' => $visit->status, 'variant' => $visitStatusClass])</td>
                            <td><a href="{{ route('emergency.show', $visit) }}" class="text-sm font-semibold text-teal-600 hover:text-teal-700">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-400">No ER visits.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="intakeModal" tabindex="-1" aria-labelledby="intakeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="intakeModalLabel">New ER Intake · AI Triage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="intakeForm" method="POST" action="{{ route('emergency.store') }}">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Patient</label>
                            <select name="patient_id" id="triagePatientId" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Select patient</option>
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->id }}">{{ $patient->full_name }} — {{ $patient->mrn }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Arrival date/time</label>
                            <input type="datetime-local" name="arrived_at" id="triageArrivedAt" value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Arrival method</label>
                            <select name="arrival_method" id="triageArrivalMethod" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Select</option>
                                <option value="Walk-in">Walk-in</option>
                                <option value="Ambulance">Ambulance</option>
                                <option value="Referral">Referral</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Chief complaint</label>
                            <textarea name="chief_complaint" id="triageComplaint" rows="3" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Referral details</label>
                            <textarea name="referral_details" id="triageReferral" rows="2" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Pain score (0-10)</label>
                            <input type="number" min="0" max="10" id="triagePain" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Observed symptoms</label>
                            <input type="text" id="triageSymptoms" placeholder="e.g. chest pain, shortness of breath" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="button" id="runAiBtn" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Run AI triage</button>
                    </div>
                    <div id="aiResult" class="mt-4 hidden rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div id="aiResultBody" class="text-sm text-slate-700"></div>
                    </div>
                    <div class="mt-5 flex justify-end gap-3">
                        <button type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm text-slate-700" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmQueueBtn" class="hidden rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Confirm &amp; add to queue</button>
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
                            <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-rose-700">${label}</span>
                            <span class="text-xs text-slate-500">confidence ${confidence}%</span>
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
