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

<form method="POST" action="{{ route('emergency.triage', $visit) }}" data-patient-id="{{ $visit->patient_id }}" class="space-y-4">
    @csrf

    <input type="hidden" name="ai_confirmed" id="ai_confirmed" value="0">
    <input type="hidden" name="priority_override" id="priority_override" value="">

    @php($assessment = $visit->triageAssessments->sortByDesc('triaged_at')->first())
    @php($vitals = $assessment?->triageVital)
    @php($currentPriority = match (strtolower((string) ($assessment?->priority ?? ''))) {
        'emergency', 'level 1' => 'Level 1',
        'urgent', 'level 2' => 'Level 2',
        'prompt', 'level 3' => 'Level 3',
        'non-urgent', 'level 4' => 'Level 4',
        'routine', 'level 5' => 'Level 5',
        default => '',
    })
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <h3 class="text-sm font-semibold uppercase tracking-[0.12em] text-slate-500">Clinical and arrival summary</h3>
        <dl class="mt-4 grid gap-4 text-sm md:grid-cols-2">
            <div><dt class="text-slate-500">Patient</dt><dd class="font-semibold text-slate-900">{{ $visit->patient?->full_name }} · {{ $visit->patient?->mrn }}</dd></div>
            <div><dt class="text-slate-500">Arrival</dt><dd class="font-medium text-slate-900">{{ $visit->arrived_at?->format('M d, Y g:i A') }} · {{ $visit->arrival_method ?: '—' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-slate-500">Chief complaint</dt><dd class="font-medium text-slate-900">{{ $assessment?->chief_complaint ?: $visit->chief_complaint }}</dd></div>
            <div><dt class="text-slate-500">Symptoms</dt><dd class="font-medium text-slate-900">{{ is_array($assessment?->symptoms) ? implode(', ', $assessment->symptoms) : ($assessment?->symptoms ?: '—') }}</dd></div>
            <div><dt class="text-slate-500">Pain score</dt><dd class="font-medium text-slate-900">{{ $assessment?->pain_score ?? '—' }}/10</dd></div>
            <div><dt class="text-slate-500">Blood pressure</dt><dd class="font-medium text-slate-900">{{ $vitals?->blood_pressure ?: '—' }}</dd></div>
            <div><dt class="text-slate-500">Heart rate</dt><dd class="font-medium text-slate-900">{{ $vitals?->heart_rate ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">Respiratory rate</dt><dd class="font-medium text-slate-900">{{ $vitals?->respiratory_rate ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">Temperature</dt><dd class="font-medium text-slate-900">{{ $vitals?->temperature ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">SpO₂</dt><dd class="font-medium text-slate-900">{{ $vitals?->spo2 ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">AI priority</dt><dd class="font-medium text-slate-900">{{ $assessment?->priority ?: '—' }}</dd></div>
            <div><dt class="text-slate-500">Severity score</dt><dd class="font-medium text-slate-900">{{ $assessment?->priority_score ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">Priority band</dt><dd class="font-medium text-slate-900">{{ $assessment?->triage_color ? ucfirst($assessment->triage_color) : '—' }}</dd></div>
        </dl>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Priority confirmation / override *</label>
        <select name="priority" id="priority_select" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            <option value="">Select</option>
            <option value="Level 1" {{ $currentPriority === 'Level 1' ? 'selected' : '' }}>Level 1 — Critical</option>
            <option value="Level 2" {{ $currentPriority === 'Level 2' ? 'selected' : '' }}>Level 2</option>
            <option value="Level 3" {{ $currentPriority === 'Level 3' ? 'selected' : '' }}>Level 3</option>
            <option value="Level 4" {{ $currentPriority === 'Level 4' ? 'selected' : '' }}>Level 4</option>
            <option value="Level 5" {{ $currentPriority === 'Level 5' ? 'selected' : '' }}>Level 5 — Non-urgent</option>
        </select>
    </div>

    <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
        <input type="checkbox" id="ai-confirmed-toggle" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
        <span>I confirm the recommended priority, or I have applied a clinical override and documented the reason.</span>
    </label>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Treatment Area</label>
        <input type="text" name="treatment_area" placeholder="Resus, Bay 1..." class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Queue notes</label>
        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></textarea>
    </div>

    <button type="submit" class="px-6 py-2.5 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700">Confirm priority &amp; add to queue</button>
</form>
