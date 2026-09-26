@extends('layouts.hims')

@section('title', 'Confirm Patient Registration')
@section('page-kicker', 'Emergency')
@section('page-title', 'Confirm Patient Registration')
@section('page-badge', 'Pre-arrival review')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    @include('emergency._workflow-steps', ['currentStep' => 2])

    <div class="panel-card p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Review pre-registered patient</h2>
                <p class="mt-1 text-sm text-slate-600">Confirm the information below to update the patient record and register the arrival. No re-entry is required.</p>
            </div>
            <a href="{{ route('emergency.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancel</a>
        </div>
    </div>

    <div class="panel-card p-6">
        <dl class="grid gap-5 text-sm md:grid-cols-2">
            <div><dt class="text-slate-500">Reference code</dt><dd class="font-semibold text-slate-900">{{ $profile->reference_code }}</dd></div>
            <div><dt class="text-slate-500">Patient name</dt><dd class="font-semibold text-slate-900">{{ trim(implode(' ', array_filter([$profile->first_name ?? $patient->first_name, $profile->middle_name ?? $patient->middle_name, $profile->last_name ?? $patient->last_name, $profile->suffix ?? $patient->suffix]))) }}</dd></div>
            <div><dt class="text-slate-500">Date of birth</dt><dd class="font-medium text-slate-900">{{ $profile->date_of_birth?->format('Y-m-d') ?? $patient->date_of_birth?->format('Y-m-d') ?? 'Not provided' }}</dd></div>
            <div><dt class="text-slate-500">Sex</dt><dd class="font-medium text-slate-900">{{ $profile->sex ?? $patient->sex ?? 'Not provided' }}</dd></div>
            <div><dt class="text-slate-500">Phone</dt><dd class="font-medium text-slate-900">{{ $profile->phone ?? $patient->phone ?? 'Not provided' }}</dd></div>
            <div><dt class="text-slate-500">Email</dt><dd class="font-medium text-slate-900">{{ $profile->email ?? $patient->email ?? 'Not provided' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-slate-500">Address</dt><dd class="font-medium text-slate-900">{{ trim(implode(', ', array_filter([$profile->address_line1, $profile->address_barangay, $profile->address_city, $profile->address_province, $profile->address_postal]))) ?: 'Not provided' }}</dd></div>
            <div><dt class="text-slate-500">Civil status</dt><dd class="font-medium text-slate-900">{{ $profile->civil_status ?? $patient->civil_status ?? 'Not provided' }}</dd></div>
            <div><dt class="text-slate-500">Nationality</dt><dd class="font-medium text-slate-900">{{ $profile->nationality ?? $patient->nationality ?? 'Not provided' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-slate-500">Allergies</dt><dd class="font-medium text-slate-900">{{ $profile->allergies ?: 'None provided' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-slate-500">Medical history</dt><dd class="font-medium text-slate-900">{{ $profile->medical_history ?: 'None provided' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-slate-500">Current medications</dt><dd class="font-medium text-slate-900">{{ $profile->current_medications ?: 'None provided' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-slate-500">Emergency contact</dt><dd class="font-medium text-slate-900">{{ trim(implode(' / ', array_filter([$profile->emergency_name, $profile->emergency_relationship, $profile->emergency_phone]))) ?: 'Not provided' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-slate-500">Visit reason</dt><dd class="font-medium text-slate-900">{{ $profile->visit_reason }}</dd></div>
            <div class="md:col-span-2"><dt class="text-slate-500">Initial notes</dt><dd class="font-medium text-slate-900">{{ $profile->initial_notes ?: 'None provided' }}</dd></div>
        </dl>

        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-800">
            Confirming updates the existing patient record, upserts the primary address and emergency contact, and marks this pre-arrival profile as arrived.
        </div>

        <form method="POST" action="{{ route('emergency.checkin.confirm') }}" class="mt-6 flex justify-end">
            @csrf
            <input type="hidden" name="token" value="{{ $profile->token }}">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">Confirm Registration</button>
        </form>
    </div>
</div>
@endsection
