@if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm leading-6 text-red-800">
        <strong>Please fix the following errors:</strong>
        <ul class="mt-2 list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('emergency.store') }}" class="space-y-6">
    @csrf

    @if (!empty($prefill['triage_assessment_id']))
        <input type="hidden" name="triage_assessment_id" value="{{ $prefill['triage_assessment_id'] }}">
    @endif

    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-800">
        @if (!empty($prefill['triage_assessment_id']))
            Review the pre-filled triage details and complete the arrival information as needed. After you register the arrival, you'll be taken to the visit page to confirm the priority and add the patient to the active ER queue.
        @else
            Record the essential arrival details below to move the patient into the ER workflow. After you register the arrival, you'll be taken to the visit page to confirm the priority and add the patient to the active ER queue.
        @endif
    </div>

    @if (!empty($prefill['triage_assessment_id']))
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <h3 class="text-sm font-semibold uppercase tracking-[0.12em] text-slate-500">Clinical assessment summary</h3>
        <dl class="mt-4 grid gap-4 text-sm md:grid-cols-2">
            <div class="md:col-span-2"><dt class="text-slate-500">Patient</dt><dd class="font-semibold text-slate-900">{{ $patients->firstWhere('id', $prefill['patient_id'] ?? null)?->full_name ?? '—' }} · {{ $patients->firstWhere('id', $prefill['patient_id'] ?? null)?->mrn ?? '' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-slate-500">Chief complaint</dt><dd class="font-medium text-slate-900">{{ $prefill['chief_complaint'] ?: '—' }}</dd></div>
            <div><dt class="text-slate-500">Symptoms</dt><dd class="font-medium text-slate-900">{{ $prefill['symptoms'] ?: '—' }}</dd></div>
            <div><dt class="text-slate-500">Pain score</dt><dd class="font-medium text-slate-900">{{ $prefill['pain_score'] ?? '—' }}/10</dd></div>
            <div><dt class="text-slate-500">Blood pressure</dt><dd class="font-medium text-slate-900">{{ $prefill['blood_pressure'] ?: '—' }}</dd></div>
            <div><dt class="text-slate-500">Heart rate</dt><dd class="font-medium text-slate-900">{{ $prefill['heart_rate'] ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">Respiratory rate</dt><dd class="font-medium text-slate-900">{{ $prefill['respiratory_rate'] ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">Temperature</dt><dd class="font-medium text-slate-900">{{ $prefill['temperature'] ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">SpO₂</dt><dd class="font-medium text-slate-900">{{ $prefill['spo2'] ?? '—' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-slate-500">Nurse notes</dt><dd class="font-medium text-slate-900">{{ $prefill['referral_details'] ?: '—' }}</dd></div>
        </dl>
    </div>
    @else
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <h3 class="text-sm font-semibold uppercase tracking-[0.12em] text-slate-500">New arrival</h3>
        <div class="mt-4 grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Patient</label>
                <select name="patient_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                    <option value="">Select patient</option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}">{{ $patient->full_name }} — {{ $patient->mrn }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Chief complaint</label>
                        <textarea name="chief_complaint" required rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">{{ old('chief_complaint', $prefill['chief_complaint'] ?? '') }}</textarea>
            </div>
        </div>
    </div>
    @endif

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Arrival date/time</label>
            <input type="datetime-local" name="arrived_at" value="{{ old('arrived_at', $prefill['arrived_at'] ?? now()->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Arrival method</label>
            <select name="arrival_method" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                <option value="">Select</option>
                <option value="Walk-in" {{ old('arrival_method', $prefill['arrival_method'] ?? '') == 'Walk-in' ? 'selected' : '' }}>Walk-in</option>
                <option value="Ambulance" {{ old('arrival_method', $prefill['arrival_method'] ?? '') == 'Ambulance' ? 'selected' : '' }}>Ambulance</option>
                <option value="Referral" {{ old('arrival_method', $prefill['arrival_method'] ?? '') == 'Referral' ? 'selected' : '' }}>Referral</option>
                <option value="Police" {{ old('arrival_method', $prefill['arrival_method'] ?? '') == 'Police' ? 'selected' : '' }}>Police</option>
                <option value="Other" {{ old('arrival_method', $prefill['arrival_method'] ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>

    </div>

    <details class="mt-6 rounded-xl border border-slate-200 bg-slate-50">
        <summary class="cursor-pointer list-none p-4 text-sm font-semibold text-slate-700">Advanced Details</summary>
        <div class="border-t border-slate-200 p-4">
            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Registration note</label>
                    <textarea name="referral_details" rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('referral_details', '') }}</textarea>
                </div>
            </div>
        </div>
    </details>

    <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
        <a href="{{ route('emergency.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancel</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-200">Register arrival &amp; continue</button>
    </div>
</form>
