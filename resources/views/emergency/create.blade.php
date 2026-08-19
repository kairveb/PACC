@extends('layouts.hims')

@section('title', 'ER Intake')
@section('page-kicker', 'Emergency')
@section('page-title', 'ER Intake')
@section('page-badge', 'ER intake')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="panel-card p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">ER intake</h2>
                <p class="mt-1 text-sm text-slate-600">Review the patient arrival details and record the essential information needed to move the case into the ER queue.</p>
            </div>
            <a href="{{ route('emergency.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Back to ER queue</a>
        </div>
    </div>

    @if (!empty($prefill['checkin_summary']))
        <div class="panel-card p-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-slate-900">Pre-arrival check-in summary</h3>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">Checked in via token</span>
            </div>

            <dl class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Patient</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $prefill['checkin_summary']['patient_name'] }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Age</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $prefill['checkin_summary']['age'] }} years</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Contact</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $prefill['checkin_summary']['contact_phone'] }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Address</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $prefill['checkin_summary']['address'] }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-2">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Medical history</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $prefill['checkin_summary']['medical_history'] ?: 'Not provided' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-2">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Allergies</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $prefill['checkin_summary']['allergies'] ?: 'Not provided' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-2">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Current medications</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $prefill['checkin_summary']['current_medications'] ?: 'Not provided' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-2">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Emergency contact</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $prefill['checkin_summary']['emergency_contact'] ?: 'Not provided' }}</dd>
                </div>
            </dl>
        </div>
    @endif

    <form method="POST" action="{{ route('emergency.store') }}" class="space-y-6">
        @csrf

        @if (!empty($prefill['triage_assessment_id']))
            <input type="hidden" name="triage_assessment_id" value="{{ $prefill['triage_assessment_id'] }}">
        @endif

        <div class="panel-card p-6">
            <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-800">
                @if (!empty($prefill['triage_assessment_id']))
                    Review the pre-filled triage details and complete the arrival information as needed.
                @else
                    Record the essential arrival details below to move the patient into the ER workflow.
                @endif
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Patient</label>
                    <select name="patient_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        <option value="">Select patient</option>
                        @foreach ($patients as $patient)
                            <option value="{{ $patient->id }}" {{ old('patient_id', $prefill['patient_id'] ?? '') == $patient->id ? 'selected' : '' }}>{{ $patient->full_name }} — {{ $patient->mrn }}</option>
                        @endforeach
                    </select>
                </div>

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

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Chief complaint</label>
                    <textarea name="chief_complaint" required rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('chief_complaint', $prefill['chief_complaint'] ?? '') }}</textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Pain score</label>
                    <input type="number" min="0" max="10" name="pain_score" value="{{ old('pain_score', $prefill['pain_score'] ?? 0) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Symptoms</label>
                    <input type="text" name="symptoms" value="{{ old('symptoms', $prefill['symptoms'] ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100" placeholder="Breathlessness, fever, dizziness">
                </div>
            </div>

            <details class="mt-6 rounded-xl border border-slate-200 bg-slate-50">
                <summary class="cursor-pointer list-none p-4 text-sm font-semibold text-slate-700">
                    Advanced Details
                </summary>
                <div class="border-t border-slate-200 p-4">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Blood pressure</label>
                            <input type="text" name="blood_pressure" value="{{ old('blood_pressure', $prefill['blood_pressure'] ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100" placeholder="120/80">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Heart rate</label>
                            <input type="number" name="heart_rate" min="0" max="220" value="{{ old('heart_rate', $prefill['heart_rate'] ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Respiratory rate</label>
                            <input type="number" name="respiratory_rate" min="0" max="80" value="{{ old('respiratory_rate', $prefill['respiratory_rate'] ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Temperature</label>
                            <input type="number" step="0.1" name="temperature" min="30" max="45" value="{{ old('temperature', $prefill['temperature'] ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">SpO₂</label>
                            <input type="number" name="spo2" min="0" max="100" value="{{ old('spo2', $prefill['spo2'] ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Referral / triage notes</label>
                            <textarea name="referral_details" rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('referral_details', $prefill['referral_details'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </details>

            <div class="mt-6 flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                <a href="{{ route('emergency.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-200">Register arrival</button>
            </div>
        </div>
    </form>
</div>
@endsection
