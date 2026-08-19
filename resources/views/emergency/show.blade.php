@extends('layouts.hims')

@section('title', 'ER Visit')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">ER Visit {{ $visit->visit_number }}</h1>
        <p class="text-sm text-slate-500 mt-1">Patient: <span class="font-medium">{{ $visit->patient->full_name ?? '—' }}</span> · {{ $visit->patient->mrn ?? '' }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Visit details --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-800 mb-4">Arrival Information</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-slate-500">Arrived:</span> <span class="font-medium">{{ $visit->arrived_at->format('M d, Y g:i A') }}</span></div>
                <div><span class="text-slate-500">Method:</span> <span class="font-medium">{{ $visit->arrival_method ?? '—' }}</span></div>
                <div class="col-span-2"><span class="text-slate-500">Complaint:</span> <span class="font-medium">{{ $visit->chief_complaint }}</span></div>
                <div class="col-span-2"><span class="text-slate-500">Status:</span> <span class="px-2 py-1 text-xs rounded-full bg-slate-100 ml-1">{{ $visit->status }}</span></div>
            </div>

            @if ($visit->queue)
            <div class="mt-4 p-3 bg-slate-50 rounded-lg">
                <h4 class="text-sm font-medium text-slate-700 mb-2">ER Queue</h4>
                <div class="flex justify-between text-sm">
                    <span>Priority: <strong>{{ $visit->queue->priority }}</strong></span>
                    <span>Status: {{ $visit->queue->status }}</span>
                    <span>Area: {{ $visit->queue->treatment_area ?? '—' }}</span>
                </div>
                <form method="POST" action="{{ route('emergency.queue-status', $visit->queue) }}" class="mt-3 flex gap-2">
                    @csrf
                    <select name="status" class="px-3 py-2 text-sm border border-slate-300 rounded-lg">
                        <option value="WAITING" {{ $visit->queue->status === 'WAITING' ? 'selected' : '' }}>Waiting</option>
                        <option value="IN_TREATMENT" {{ $visit->queue->status === 'IN_TREATMENT' ? 'selected' : '' }}>In Treatment</option>
                        <option value="DONE" {{ $visit->queue->status === 'DONE' ? 'selected' : '' }}>Done</option>
                    </select>
                    <select name="provider_id" class="px-3 py-2 text-sm border border-slate-300 rounded-lg">
                        <option value="">Provider</option>
                        @foreach ($providers as $provider)
                            <option value="{{ $provider->id }}">{{ $provider->full_name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 text-sm bg-slate-800 text-white rounded-lg">Update</button>
                </form>
            </div>
            @endif
        </div>

        {{-- Triage form --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="font-semibold text-slate-800">Triage Assessment</h3>
                <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">AI-assisted</span>
            </div>

            <form method="POST" action="{{ route('emergency.triage', $visit) }}" data-patient-id="{{ $visit->patient_id }}" class="space-y-4">
                @csrf

                <input type="hidden" name="patient_id" value="{{ $visit->patient_id }}">
                <input type="hidden" name="ai_confirmed" id="ai_confirmed" value="0">
                <input type="hidden" name="priority_override" id="priority_override" value="">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Chief Complaint</label>
                    <textarea name="chief_complaint" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">{{ $visit->chief_complaint }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Symptoms</label>
                    <input type="text" name="symptoms" placeholder="e.g. chest pain, shortness of breath, dizziness" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Pain Score (0-10)</label>
                        <input type="number" name="pain_score" min="0" max="10" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Priority *</label>
                        <select name="priority" id="priority_select" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                            <option value="">Select</option>
                            <option value="Level 1">Level 1 — Critical</option>
                            <option value="Level 2">Level 2</option>
                            <option value="Level 3">Level 3</option>
                            <option value="Level 4">Level 4</option>
                            <option value="Level 5">Level 5 — Non-urgent</option>
                        </select>
                    </div>
                </div>

                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4" id="ai-preview">
                    <div class="text-sm leading-6 text-slate-600">No assessment has been run yet. Complete the intake details and generate a priority recommendation.</div>
                </div>

                <div class="grid gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between"><span class="font-medium text-slate-600">Severity score</span><strong id="severity-score-display">—</strong></div>
                    <div class="flex items-center justify-between"><span class="font-medium text-slate-600">Priority band</span><strong id="priority-band-display">—</strong></div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="button" id="run-ai" class="px-4 py-2.5 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700">Generate recommendation</button>
                    <button type="button" id="clinical-override" class="px-4 py-2.5 text-sm font-medium border border-slate-300 bg-white text-slate-700 rounded-lg hover:bg-slate-100">Clinical override</button>
                </div>

                <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
                    <input type="checkbox" id="ai-confirmed-toggle" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span>I confirm the recommended priority, or I have applied a clinical override and documented the reason.</span>
                </label>

                <div>
                    <h4 class="text-sm font-medium text-slate-700 mb-2">Vital Signs</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-xs text-slate-500 mb-1">Blood Pressure</label><input name="vitals_blood_pressure" placeholder="120/80" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                        <div><label class="block text-xs text-slate-500 mb-1">Heart Rate</label><input type="number" name="vitals_heart_rate" placeholder="bpm" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                        <div><label class="block text-xs text-slate-500 mb-1">Respiratory Rate</label><input type="number" name="vitals_respiratory_rate" placeholder="/min" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                        <div><label class="block text-xs text-slate-500 mb-1">Temperature (°C)</label><input type="number" step="0.1" name="vitals_temperature" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                        <div><label class="block text-xs text-slate-500 mb-1">SpO2 (%)</label><input type="number" name="vitals_spo2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Treatment Area</label>
                    <input type="text" name="treatment_area" placeholder="Resus, Bay 1..." class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></textarea>
                </div>

                <button type="submit" class="px-6 py-2.5 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700">Complete Triage</button>
            </form>
        </div>
    </div>

    {{-- Triage history --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200">
            <h3 class="font-semibold text-slate-800">Triage History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                        <th class="py-3 px-4">Time</th><th class="py-3 px-4">Priority</th><th class="py-3 px-4">Pain</th><th class="py-3 px-4">Vitals</th><th class="py-3 px-4">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visit->triageAssessments as $ta)
                        <tr class="border-b border-slate-100">
                            <td class="py-3 px-4">{{ $ta->triaged_at->format('M d, g:i A') }}</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">{{ $ta->priority }}</span></td>
                            <td class="py-3 px-4">{{ $ta->pain_score ?? '—' }}/10</td>
                            <td class="py-3 px-4 text-xs">
                                @if ($ta->triageVital)
                                    BP {{ $ta->triageVital->blood_pressure ?? '—' }} · HR {{ $ta->triageVital->heart_rate ?? '—' }} · T {{ $ta->triageVital->temperature ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="py-3 px-4">{{ $ta->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-400">No triage assessments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const form = document.querySelector('form[data-patient-id]');
        if (!form) return;

        const preview = document.getElementById('ai-preview');
        const runAiButton = document.getElementById('run-ai');
        const severityDisplay = document.getElementById('severity-score-display');
        const bandDisplay = document.getElementById('priority-band-display');
        const aiConfirmedToggle = document.getElementById('ai-confirmed-toggle');
        const aiConfirmedField = document.getElementById('ai_confirmed');
        const priorityOverrideField = document.getElementById('priority_override');
        const selectField = document.getElementById('priority_select');
        const overrideModal = document.createElement('div');
        overrideModal.className = 'fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4';
        overrideModal.innerHTML = `
            <div class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-5 shadow-2xl">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-amber-600">Clinical safety</p>
                        <h3 class="mt-1 text-xl font-semibold text-slate-900">Clinical override</h3>
                    </div>
                    <button type="button" class="close-override rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-600 hover:bg-slate-100">Close</button>
                </div>
                <div class="mt-5 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Override priority</label>
                        <select id="override-level" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                            <option value="Emergency">Emergency — immediate escalation</option>
                            <option value="Urgent">Urgent — rapid review</option>
                            <option value="Prompt">Prompt — timely assessment</option>
                            <option value="Non-Urgent">Non-Urgent — standard review</option>
                            <option value="Routine">Routine — routine follow-up</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Override rationale</label>
                        <textarea id="override-notes" rows="4" placeholder="Explain why the AI recommendation was adjusted by the clinical team." class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" class="cancel-override inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancel</button>
                        <button type="button" class="apply-override inline-flex items-center justify-center rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-amber-400">Apply override</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(overrideModal);

        function syncAiConfirmationState(checked) {
            if (aiConfirmedField) aiConfirmedField.value = checked ? '1' : '0';
            if (aiConfirmedToggle) aiConfirmedToggle.checked = checked;
        }

        function closeOverrideModal() {
            overrideModal.classList.add('hidden');
            overrideModal.classList.remove('flex');
        }

        function apiRequest(url, method = 'GET', payload = null) {
            const options = {
                method,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            };
            if (payload !== null) {
                options.body = JSON.stringify(payload);
                options.headers['Content-Type'] = 'application/json';
            }
            return window.HimsApi?.request?.(url, options) ?? fetch(url, options).then(async (response) => {
                const text = await response.text();
                let data = null;
                try { data = text ? JSON.parse(text) : null; } catch (e) { data = null; }
                if (!response.ok) {
                    throw new Error(data?.message || 'Request failed.');
                }
                return { data: data?.data ?? data };
            });
        }

        function renderAssessment(result) {
            const priority = result.priority || 'Routine';
            const confidence = result.confidence || 80;
            const color = result.color || 'green';
            const severityScore = result.severity_score ?? 0;
            const priorityBand = result.priority_band || 'Green';
            const badgeClasses = {
                red: 'bg-rose-600 text-white',
                yellow: 'bg-amber-500 text-slate-900',
                orange: 'bg-orange-500 text-white',
                green: 'bg-emerald-600 text-white',
            };
            const reasons = (result.reasons || []).map((reason) => `<li>${reason}</li>`).join('');

            if (severityDisplay) severityDisplay.textContent = `${severityScore}/100`;
            if (bandDisplay) {
                bandDisplay.textContent = priorityBand;
                bandDisplay.className = 'font-semibold ' + (priorityBand === 'Red' ? 'text-rose-600' : priorityBand === 'Yellow' ? 'text-amber-600' : 'text-emerald-600');
            }
            if (priorityOverrideField) priorityOverrideField.value = priority;
            if (selectField) selectField.value = priority === 'Emergency' ? 'Level 1' : priority === 'Urgent' ? 'Level 2' : priority === 'Prompt' ? 'Level 3' : priority === 'Non-Urgent' ? 'Level 4' : 'Level 5';
            syncAiConfirmationState(false);

            if (preview) {
                preview.innerHTML = `
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] ${badgeClasses[color] || badgeClasses.green}">${priority}</span>
                        <span class="text-xs font-medium text-slate-500">Confidence ${confidence}%</span>
                    </div>
                    <div class="space-y-3 text-sm text-slate-700">
                        <p class="text-base font-semibold text-slate-900">AI recommendation</p>
                        <p class="leading-6">${result.recommendation || 'No specific concerns identified.'}</p>
                        <ul class="list-disc space-y-1 pl-5 text-slate-600">${reasons || '<li>No acute red flags were detected.</li>'}</ul>
                    </div>
                `;
            }
        }

        if (aiConfirmedToggle) {
            aiConfirmedToggle.addEventListener('change', function () {
                syncAiConfirmationState(this.checked);
            });
        }

        if (runAiButton) {
            runAiButton.addEventListener('click', async function () {
                const complaint = form.querySelector('[name="chief_complaint"]')?.value?.trim();
                const symptoms = (form.querySelector('[name="symptoms"]')?.value ?? '')
                    .split(/[\n,;]/)
                    .map((value) => value.trim())
                    .filter(Boolean);

                if (!complaint) {
                    if (window.hisToast) hisToast('Enter a chief complaint before running AI triage.', 'warning');
                    return;
                }

                try {
                    const response = await apiRequest('/api/v1/triage/score', 'POST', {
                        patient_id: Number(form.dataset.patientId || form.querySelector('[name="patient_id"]').value),
                        chief_complaint: complaint,
                        pain_score: Number(form.querySelector('[name="pain_score"]')?.value || 0),
                        symptoms,
                        vitals: {
                            blood_pressure: form.querySelector('[name="vitals_blood_pressure"]')?.value || null,
                            heart_rate: Number(form.querySelector('[name="vitals_heart_rate"]')?.value || 0) || null,
                            respiratory_rate: Number(form.querySelector('[name="vitals_respiratory_rate"]')?.value || 0) || null,
                            temperature: Number(form.querySelector('[name="vitals_temperature"]')?.value || 0) || null,
                            spo2: Number(form.querySelector('[name="vitals_spo2"]')?.value || 0) || null,
                        },
                    });

                    renderAssessment(response.data || {});

                    if (window.hisToast) hisToast('AI triage completed. ' + (response.data?.priority || 'Routine') + ' priority assigned.', 'success');
                } catch (error) {
                    if (window.hisToast) hisToast(error.message, 'danger');
                }
            });
        }

        document.getElementById('clinical-override')?.addEventListener('click', function () {
            overrideModal.classList.remove('hidden');
            overrideModal.classList.add('flex');
        });

        overrideModal.querySelector('.close-override')?.addEventListener('click', closeOverrideModal);
        overrideModal.querySelector('.cancel-override')?.addEventListener('click', closeOverrideModal);
        overrideModal.querySelector('.apply-override')?.addEventListener('click', function () {
            const selectedPriority = overrideModal.querySelector('#override-level')?.value || 'Routine';
            const rationale = overrideModal.querySelector('#override-notes')?.value?.trim() || 'Clinician override applied after AI review.';
            const notesField = form.querySelector('[name="notes"]');

            if (notesField) {
                notesField.value = `Clinical override applied: ${selectedPriority}. Reason: ${rationale}`;
            }
            if (priorityOverrideField) priorityOverrideField.value = selectedPriority;
            if (selectField) selectField.value = selectedPriority === 'Emergency' ? 'Level 1' : selectedPriority === 'Urgent' ? 'Level 2' : selectedPriority === 'Prompt' ? 'Level 3' : selectedPriority === 'Non-Urgent' ? 'Level 4' : 'Level 5';
            syncAiConfirmationState(true);

            if (bandDisplay) {
                bandDisplay.textContent = selectedPriority === 'Emergency' ? 'Red' : selectedPriority === 'Urgent' ? 'Yellow' : selectedPriority === 'Prompt' ? 'Green' : selectedPriority === 'Non-Urgent' ? 'Green' : 'Green';
                bandDisplay.className = 'font-semibold ' + (selectedPriority === 'Emergency' ? 'text-rose-600' : selectedPriority === 'Urgent' ? 'text-amber-600' : 'text-emerald-600');
            }

            if (severityDisplay) severityDisplay.textContent = selectedPriority === 'Emergency' ? '96/100' : selectedPriority === 'Urgent' ? '85/100' : selectedPriority === 'Prompt' ? '68/100' : '46/100';

            if (preview) {
                preview.innerHTML = `
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <span class="inline-flex rounded-full bg-amber-500 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-900">${selectedPriority}</span>
                        <span class="text-xs font-medium text-slate-500">Clinician override</span>
                    </div>
                    <div class="space-y-3 text-sm text-slate-700">
                        <p class="text-base font-semibold text-slate-900">Clinical review</p>
                        <p class="leading-6">The AI suggestion was reviewed and adjusted by the assigned clinical team to ${selectedPriority} priority.</p>
                        <p class="rounded-xl border border-amber-200 bg-amber-50 p-2 text-slate-700">${rationale}</p>
                    </div>
                `;
            }

            if (window.hisToast) hisToast('Clinical override saved to the triage notes.', 'success');
            closeOverrideModal();
        });
    })();
</script>
@endpush
@endsection
