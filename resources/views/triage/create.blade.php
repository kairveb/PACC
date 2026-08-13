@extends('layouts.hims')

@section('title', 'AI Triage Assessment')
@section('page-kicker', 'Nursing')
@section('page-title', 'AI Triage Assessment')
@section('page-badge', 'AI triage')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <div class="panel-card p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Patient triage intake</h2>
                <p class="mt-1 text-sm text-slate-600">Capture the chief complaint, symptoms, and vitals to generate an AI-assisted urgency recommendation.</p>
            </div>
            <a href="{{ route('emergency.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Open ER queue</a>
        </div>
    </div>

    <form method="POST" action="{{ route('triage.store') }}" class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
        @csrf

        <div class="space-y-6">
            <div class="panel-card p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">Clinical intake</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="patient_id" class="mb-1 block text-sm font-medium text-slate-700">Patient</label>
                        <select name="patient_id" id="patient_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                            <option value="">Select patient</option>
                            @foreach ($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->full_name }} — {{ $patient->mrn }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="chief_complaint" class="mb-1 block text-sm font-medium text-slate-700">Chief complaint</label>
                        <textarea name="chief_complaint" id="chief_complaint" rows="3" required placeholder="e.g. Difficulty breathing and chest pain" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label for="symptoms" class="mb-1 block text-sm font-medium text-slate-700">Symptoms</label>
                        <input name="symptoms" id="symptoms" type="text" placeholder="e.g. chest pain, shortness of breath, fever" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="pain_score" class="mb-1 block text-sm font-medium text-slate-700">Pain score</label>
                        <input name="pain_score" id="pain_score" type="number" min="0" max="10" value="0" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="blood_pressure" class="mb-1 block text-sm font-medium text-slate-700">Blood pressure</label>
                        <input name="blood_pressure" id="blood_pressure" type="text" placeholder="120/80" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="heart_rate" class="mb-1 block text-sm font-medium text-slate-700">Heart rate</label>
                        <input name="heart_rate" id="heart_rate" type="number" min="0" max="220" placeholder="72" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="respiratory_rate" class="mb-1 block text-sm font-medium text-slate-700">Resp. rate</label>
                        <input name="respiratory_rate" id="respiratory_rate" type="number" min="0" max="80" placeholder="18" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="temperature" class="mb-1 block text-sm font-medium text-slate-700">Temperature</label>
                        <input name="temperature" id="temperature" type="number" step="0.1" min="30" max="45" placeholder="36.8" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="spo2" class="mb-1 block text-sm font-medium text-slate-700">SpO₂</label>
                        <input name="spo2" id="spo2" type="number" min="0" max="100" placeholder="98" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="mb-1 block text-sm font-medium text-slate-700">Nurse notes</label>
                        <textarea name="notes" id="notes" rows="3" placeholder="Optional situational notes" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="panel-card p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">AI triage result</h3>
                <div id="ai-preview" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4">
                    <div class="text-sm text-slate-600">No assessment has been run yet.</div>
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <button type="button" id="run-ai" class="flex-1 rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Run AI triage</button>
                    <button type="submit" class="flex-1 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Save assessment</button>
                </div>
            </div>
        </aside>
    </form>
</div>

@push('scripts')
<script>
    (function () {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;

        async function apiRequest(url, method = 'GET', payload = null) {
            const headers = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
            if (token) headers['X-CSRF-TOKEN'] = token;
            if (payload !== null) headers['Content-Type'] = 'application/json';

            const response = await fetch(url, {
                method,
                credentials: 'same-origin',
                headers,
                body: payload ? JSON.stringify(payload) : undefined,
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Request failed.');
            return data;
        }

        const preview = document.getElementById('ai-preview');
        const runAiButton = document.getElementById('run-ai');

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

                    const result = response.data || {};
                    const level = Number(result.level || 5);
                    const color = result.color || 'green';
                    const priority = result.priority || 'Routine';
                    const confidence = result.confidence || 80;
                    const reasons = (result.reasons || []).map((reason) => `<li>${reason}</li>`).join('');

                    preview.innerHTML = `
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-white bg-${color === 'red' ? 'rose' : color === 'yellow' ? 'amber' : color === 'orange' ? 'orange' : 'emerald'}-600">${priority}</span>
                            <span class="text-xs text-slate-500">confidence ${confidence}%</span>
                        </div>
                        <div class="text-sm text-slate-700">
                            <p class="font-semibold text-slate-900">AI recommendation</p>
                            <p class="mt-2">${result.recommendation || 'No specific concerns identified.'}</p>
                            <ul class="mt-3 list-disc space-y-1 pl-5">${reasons || '<li>No acute red flags were detected.</li>'}</ul>
                        </div>
                    `;

                    if (window.hisToast) hisToast('AI triage completed. ' + priority + ' priority assigned.', 'success');
                } catch (error) {
                    if (window.hisToast) hisToast(error.message, 'danger');
                }
            });
        }
    })();
</script>
@endpush
@endsection
