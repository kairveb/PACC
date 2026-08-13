@extends('layouts.hims')

@section('title', 'Register ER Arrival')

@section('content')
<div class="max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Register ER Arrival</h1>
            <p class="text-sm text-slate-500 mt-1">Emergency and ER Triage System (EERTS)</p>
        </div>
        <a href="{{ route('emergency.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <form method="POST" action="{{ route('emergency.store') }}" class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
        @csrf

        @if (!empty($prefill['triage_assessment_id']))
            <input type="hidden" name="triage_assessment_id" value="{{ $prefill['triage_assessment_id'] }}">
        @endif

        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            @if (!empty($prefill['triage_assessment_id']))
                Pre-filled from the existing triage assessment. Review and update only if needed.
            @else
                New ER intake record. Complete the details below.
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Patient *</label>
            <select name="patient_id" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                <option value="">Select patient</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->id }}" {{ old('patient_id', $prefill['patient_id'] ?? '') == $patient->id ? 'selected' : '' }}>{{ $patient->full_name }} — {{ $patient->mrn }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Arrival Date/Time</label>
                <input type="datetime-local" name="arrived_at" value="{{ old('arrived_at', $prefill['arrived_at'] ?? now()->format('Y-m-d\TH:i')) }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Arrival Method</label>
                <select name="arrival_method" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                    <option value="">Select</option>
                    <option value="Walk-in" {{ old('arrival_method', $prefill['arrival_method'] ?? '') == 'Walk-in' ? 'selected' : '' }}>Walk-in</option>
                    <option value="Ambulance" {{ old('arrival_method', $prefill['arrival_method'] ?? '') == 'Ambulance' ? 'selected' : '' }}>Ambulance</option>
                    <option value="Referral" {{ old('arrival_method', $prefill['arrival_method'] ?? '') == 'Referral' ? 'selected' : '' }}>Referral</option>
                    <option value="Police" {{ old('arrival_method', $prefill['arrival_method'] ?? '') == 'Police' ? 'selected' : '' }}>Police</option>
                    <option value="Other" {{ old('arrival_method', $prefill['arrival_method'] ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Chief Complaint *</label>
                <textarea name="chief_complaint" required rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">{{ old('chief_complaint', $prefill['chief_complaint'] ?? '') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Symptoms</label>
                <textarea name="symptoms" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">{{ old('symptoms', $prefill['symptoms'] ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Pain Score</label>
                <input type="number" min="0" max="10" name="pain_score" value="{{ old('pain_score', $prefill['pain_score'] ?? 0) }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Blood Pressure</label>
                <input type="text" name="blood_pressure" value="{{ old('blood_pressure', $prefill['blood_pressure'] ?? '') }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg" placeholder="120/80">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Heart Rate</label>
                <input type="number" name="heart_rate" min="0" max="220" value="{{ old('heart_rate', $prefill['heart_rate'] ?? '') }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Resp. Rate</label>
                <input type="number" name="respiratory_rate" min="0" max="80" value="{{ old('respiratory_rate', $prefill['respiratory_rate'] ?? '') }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Temperature</label>
                <input type="number" step="0.1" name="temperature" min="30" max="45" value="{{ old('temperature', $prefill['temperature'] ?? '') }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">SpO₂</label>
                <input type="number" name="spo2" min="0" max="100" value="{{ old('spo2', $prefill['spo2'] ?? '') }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Referral / Triage Notes</label>
            <textarea name="referral_details" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">{{ old('referral_details', $prefill['referral_details'] ?? '') }}</textarea>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700">Register Arrival</button>
            <a href="{{ route('emergency.index') }}" class="px-6 py-2.5 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
