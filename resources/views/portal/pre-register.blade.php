@extends('layouts.hims')

@section('title', 'Pre-registration')
@section('page-kicker', 'Patient portal')
@section('page-title', 'Pre-registration')
@section('page-badge', 'Secure arrival')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="panel-card p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Pre-registration</h2>
                <p class="mt-1 text-sm text-slate-600">Provide your arrival information before your visit so staff can prepare your intake quickly and accurately.</p>
            </div>
            <a href="{{ route('patients.portal') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Back to dashboard</a>
        </div>
    </div>

    <form method="POST" action="{{ route('portal.pre-register.store') }}" class="space-y-6">
        @csrf

        <div class="panel-card p-6">
            <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-800">
                This form is for your pre-arrival information only. Clinical or vital-sign fields are intentionally left blank and will be completed by staff during intake.
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-slate-900">Patient details</h3>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Patient name</label>
                    <input type="text" value="{{ $patient->full_name }}" disabled class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm text-slate-700">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Age</label>
                    <input type="text" value="{{ $patient->age }} years" disabled class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm text-slate-700">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $patient->phone) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="09xxxxxxxxx">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $patient->email) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Home address</label>
                    <input type="text" name="address_line1" value="{{ old('address_line1', $address->line1 ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="House number, street, subdivision">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">City</label>
                    <input type="text" name="address_city" value="{{ old('address_city', $address->city ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Province</label>
                    <input type="text" name="address_province" value="{{ old('address_province', $address->province ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Postal code</label>
                    <input type="text" name="address_postal_code" value="{{ old('address_postal_code', $address->postal_code ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Visit reason</label>
                    <textarea name="visit_reason" rows="3" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="Briefly describe why you are coming in today.">{{ old('visit_reason') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Initial notes</label>
                    <textarea name="initial_notes" rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="Add any context the intake team should know before arrival.">{{ old('initial_notes') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-slate-900">Medical history</h3>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Medical history</label>
                    <textarea name="medical_history" rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="Asthma, hypertension, previous surgeries, chronic conditions...">{{ old('medical_history') }}</textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Current medications</label>
                    <textarea name="current_medications" rows="2" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="List medications currently being taken.">{{ old('current_medications') }}</textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Allergies</label>
                    <textarea name="allergies" rows="2" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="Penicillin, peanuts, latex...">{{ old('allergies') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-slate-900">Emergency contact</h3>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Contact name</label>
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $contact->name ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Relationship</label>
                    <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $contact->relationship ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Emergency contact phone</label>
                    <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $contact->phone ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                <a href="{{ route('patients.portal') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">Save pre-registration</button>
            </div>
        </div>
    </form>
</div>
@endsection
