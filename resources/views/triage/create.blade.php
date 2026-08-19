@extends('layouts.hims')

@section('title', 'Triage Assessment')
@section('page-kicker', 'Nursing')
@section('page-title', 'Triage Assessment')
@section('page-badge', 'Triage')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="panel-card p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Patient triage intake</h2>
                <p class="mt-1 text-sm text-slate-600">Capture the chief complaint, symptoms, and vital signs to assess urgency and move the patient into the correct care pathway.</p>
            </div>
            <a href="{{ route('emergency.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Open ER queue</a>
        </div>
    </div>

    <form method="POST" action="{{ route('triage.store') }}" class="grid gap-6 xl:grid-cols-[1.5fr_0.8fr]">
        @csrf

        <div class="space-y-6">
            <div class="panel-card p-6">
                <div class="mb-5 flex items-center justify-between border-b border-slate-200 pb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Patient intake</h3>
                    <span class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-teal-700">Required</span>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="patient_id" class="mb-1.5 block text-sm font-medium text-slate-700">Patient</label>
                        <select name="patient_id" id="patient_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                            <option value="">Select patient</option>
                            @foreach ($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->full_name }} — {{ $patient->mrn }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="chief_complaint" class="mb-1.5 block text-sm font-medium text-slate-700">Chief complaint</label>
                        <textarea name="chief_complaint" id="chief_complaint" rows="3" required placeholder="e.g. Difficulty breathing and chest pain" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label for="symptoms" class="mb-1.5 block text-sm font-medium text-slate-700">Symptoms</label>
                        <input name="symptoms" id="symptoms" type="text" placeholder="e.g. chest pain, shortness of breath, fever" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    </div>

                    <div>
                        <label for="pain_score" class="mb-1.5 block text-sm font-medium text-slate-700">Pain score</label>
                        <input name="pain_score" id="pain_score" type="number" min="0" max="10" value="0" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    </div>

                    <div>
                        <label for="blood_pressure" class="mb-1.5 block text-sm font-medium text-slate-700">Blood pressure</label>
                        <input name="blood_pressure" id="blood_pressure" type="text" placeholder="120/80" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    </div>

                    <div>
                        <label for="heart_rate" class="mb-1.5 block text-sm font-medium text-slate-700">Heart rate</label>
                        <input name="heart_rate" id="heart_rate" type="number" min="0" max="220" placeholder="72" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    </div>

                    <div>
                        <label for="respiratory_rate" class="mb-1.5 block text-sm font-medium text-slate-700">Respiratory rate</label>
                        <input name="respiratory_rate" id="respiratory_rate" type="number" min="0" max="80" placeholder="18" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    </div>

                    <div>
                        <label for="temperature" class="mb-1.5 block text-sm font-medium text-slate-700">Temperature</label>
                        <input name="temperature" id="temperature" type="number" step="0.1" min="30" max="45" placeholder="36.8" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    </div>

                    <div>
                        <label for="spo2" class="mb-1.5 block text-sm font-medium text-slate-700">SpO₂</label>
                        <input name="spo2" id="spo2" type="number" min="0" max="100" placeholder="98" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="mb-1.5 block text-sm font-medium text-slate-700">Nurse notes</label>
                        <textarea name="notes" id="notes" rows="3" placeholder="Optional situational notes" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="panel-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Priority recommendation</h3>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">Live</span>
                </div>

                <div id="ai-preview" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4">
                    <div class="text-sm leading-6 text-slate-600">No assessment has been run yet. Complete the intake details and generate a priority recommendation.</div>
                </div>

                <div class="mt-4 grid gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between"><span class="font-medium text-slate-600">Severity score</span><strong id="severity-score-display">—</strong></div>
                    <div class="flex items-center justify-between"><span class="font-medium text-slate-600">Priority band</span><strong id="priority-band-display">—</strong></div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <button type="button" id="run-ai" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-200">Generate recommendation</button>
                    <button type="button" id="clinical-override" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-200">Clinical override</button>
                </div>

                <input type="hidden" name="ai_confirmed" id="ai_confirmed" value="0">
                <input type="hidden" name="priority_override" id="priority_override" value="">

                <label class="mt-4 flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
                    <input type="checkbox" id="ai-confirmed-toggle" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span>I confirm the recommended priority, or I have applied a clinical override and documented the reason.</span>
                </label>

                <div class="mt-5">
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-200">Save triage</button>
                </div>
            </div>
        </aside>
    </form>
</div>

<div id="override-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4">
    <div class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-5 shadow-2xl">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-amber-600">Clinical safety</p>
                <h3 class="mt-1 text-xl font-semibold text-slate-900">Clinical override</h3>
            </div>
            <button type="button" id="close-override-modal" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-600 hover:bg-slate-100">Close</button>
        </div>

        <div class="mt-5 space-y-4">
            <div>
                <label for="override-level" class="mb-1.5 block text-sm font-medium text-slate-700">Override priority</label>
                <select id="override-level" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    <option value="Red">Red — immediate escalation</option>
                    <option value="Yellow">Yellow — urgent review</option>
                    <option value="Green">Green — routine follow-up</option>
                </select>
            </div>
            <div>
                <label for="override-notes" class="mb-1.5 block text-sm font-medium text-slate-700">Override rationale</label>
                <textarea id="override-notes" rows="4" placeholder="Explain why the AI recommendation was adjusted by the clinical team." class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" id="cancel-override" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancel</button>
                <button type="button" id="apply-override" class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-amber-400">Apply override</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        async function apiRequest(url, method = 'GET', payload = null) {
            const options = {
                method,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            };

            if (payload !== null) {
                options.body = payload;
            }

            return window.HimsApi?.request?.(url, options) ?? Promise.reject(new Error('API client unavailable.'));
        }

        const preview = document.getElementById('ai-preview');
        const runAiButton = document.getElementById('run-ai');
        const severityDisplay = document.getElementById('severity-score-display');
        const bandDisplay = document.getElementById('priority-band-display');
        const overrideModal = document.getElementById('override-modal');
        const clinicalOverrideButton = document.getElementById('clinical-override');
        const closeOverrideModalButton = document.getElementById('close-override-modal');
        const cancelOverrideButton = document.getElementById('cancel-override');
        const applyOverrideButton = document.getElementById('apply-override');
        const overrideNotesField = document.getElementById('override-notes');
        const overrideLevelField = document.getElementById('override-level');
        const aiConfirmedToggle = document.getElementById('ai-confirmed-toggle');
        const aiConfirmedField = document.getElementById('ai_confirmed');
        const priorityOverrideField = document.getElementById('priority_override');

        function syncAiConfirmationState(checked) {
            if (aiConfirmedField) aiConfirmedField.value = checked ? '1' : '0';
            if (aiConfirmedToggle) aiConfirmedToggle.checked = checked;
        }

        if (aiConfirmedToggle) {
            aiConfirmedToggle.addEventListener('change', function () {
                syncAiConfirmationState(this.checked);
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
            syncAiConfirmationState(false);

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

        if (runAiButton) {
            runAiButton.addEventListener('click', async function () {
                const complaint = document.getElementById('chief_complaint')?.value?.trim();
                const symptoms = (document.getElementById('symptoms')?.value ?? '')
                    .split(/[\n,;]/)
                    .map((value) => value.trim())
                    .filter(Boolean);

                if (!complaint) {
                    if (window.hisToast) hisToast('Enter a chief complaint before running AI triage.', 'warning');
                    return;
                }

                try {
                    const response = await apiRequest('/api/v1/triage/score', 'POST', {
                        chief_complaint: complaint,
                        pain_score: Number(document.getElementById('pain_score')?.value || 0),
                        symptoms,
                        vitals: {
                            blood_pressure: document.getElementById('blood_pressure')?.value || null,
                            heart_rate: Number(document.getElementById('heart_rate')?.value || 0) || null,
                            respiratory_rate: Number(document.getElementById('respiratory_rate')?.value || 0) || null,
                            temperature: Number(document.getElementById('temperature')?.value || 0) || null,
                            spo2: Number(document.getElementById('spo2')?.value || 0) || null,
                        },
                    });

                    renderAssessment(response.data || {});

                    if (window.hisToast) hisToast('AI triage completed. ' + (response.data?.priority || 'Routine') + ' priority assigned.', 'success');
                } catch (error) {
                    if (window.hisToast) hisToast(error.message, 'danger');
                }
            });
        }

        if (clinicalOverrideButton) {
            clinicalOverrideButton.addEventListener('click', function () {
                if (overrideModal) overrideModal.classList.remove('hidden');
                if (overrideModal) overrideModal.classList.add('flex');
            });
        }

        function closeOverrideModal() {
            if (overrideModal) {
                overrideModal.classList.add('hidden');
                overrideModal.classList.remove('flex');
            }
        }

        if (closeOverrideModalButton) closeOverrideModalButton.addEventListener('click', closeOverrideModal);
        if (cancelOverrideButton) cancelOverrideButton.addEventListener('click', closeOverrideModal);

        if (applyOverrideButton) {
            applyOverrideButton.addEventListener('click', function () {
                const selectedBand = overrideLevelField?.value || 'Green';
                const rationale = overrideNotesField?.value?.trim() || 'Clinician override applied after AI review.';
                const notesField = document.getElementById('notes');

                if (notesField) {
                    notesField.value = `Clinical override applied: ${selectedBand}. Reason: ${rationale}`;
                }

                if (priorityOverrideField) priorityOverrideField.value = selectedBand;
                syncAiConfirmationState(true);

                if (bandDisplay) {
                    bandDisplay.textContent = selectedBand;
                    bandDisplay.className = 'font-semibold ' + (selectedBand === 'Red' ? 'text-rose-600' : selectedBand === 'Yellow' ? 'text-amber-600' : 'text-emerald-600');
                }

                if (severityDisplay) severityDisplay.textContent = selectedBand === 'Red' ? '96/100' : selectedBand === 'Yellow' ? '85/100' : '46/100';

                if (preview) {
                    preview.innerHTML = `
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <span class="inline-flex rounded-full bg-amber-500 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-900">${selectedBand}</span>
                            <span class="text-xs font-medium text-slate-500">Clinician override</span>
                        </div>
                        <div class="space-y-3 text-sm text-slate-700">
                            <p class="text-base font-semibold text-slate-900">Clinical review</p>
                            <p class="leading-6">The AI suggestion was reviewed and adjusted by the assigned clinical team to ${selectedBand} priority.</p>
                            <p class="rounded-xl border border-amber-200 bg-amber-50 p-2 text-slate-700">${rationale}</p>
                        </div>
                    `;
                }

                if (window.hisToast) hisToast('Clinical override saved to the triage notes.', 'success');
                closeOverrideModal();
            });
        }
    })();
</script>
@endpush
@endsection
