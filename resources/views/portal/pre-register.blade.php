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
                    <h3 class="text-lg font-semibold text-slate-900">1. Essential Information</h3>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $patient->first_name) }}" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name', $patient->middle_name) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $patient->last_name) }}" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Suffix</label>
                    <input type="text" name="suffix" value="{{ old('suffix', $patient->suffix) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Date of Birth *</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Sex *</label>
                    <select name="sex" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                        <option value="">Select</option>
                        <option value="Male" {{ old('sex', $patient->sex) === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('sex', $patient->sex) === 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('sex', $patient->sex) === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Civil Status</label>
                    <input type="text" name="civil_status" value="{{ old('civil_status', $patient->civil_status) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality', $patient->nationality) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Contact Number</label>
                    <input type="tel" name="phone" value="{{ old('phone', $patient->phone) }}" data-phone-input class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" inputmode="numeric" pattern="^(09\d{9}|\+639\d{9})$" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $patient->email) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-slate-900">2. Address</h3>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Street Address</label>
                    <input type="text" name="address_line1" value="{{ old('address_line1', $address->line1 ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="House number, street, subdivision">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Province</label>
                    <input type="text" name="address_province" value="{{ old('address_province', $address->province ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">City / Municipality</label>
                    <input type="text" name="address_city" value="{{ old('address_city', $address->city ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Barangay</label>
                    <input type="text" name="address_barangay" value="{{ old('address_barangay', $address->barangay ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Postal Code</label>
                    <input type="text" name="address_postal" value="{{ old('address_postal', $address->postal_code ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Allergies / Alerts</label>
                    <textarea name="allergies" rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">{{ old('allergies', $patient->allergies) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-slate-900">Emergency Contact</h3>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Emergency Contact Name</label>
                    <input type="text" name="emergency_name" value="{{ old('emergency_name', $contact->name ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Emergency Contact Relationship</label>
                    <input type="text" name="emergency_relationship" value="{{ old('emergency_relationship', $contact->relationship ?? '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Emergency Contact Phone</label>
                    <input type="tel" name="emergency_phone" value="{{ old('emergency_phone', $contact->phone ?? '') }}" data-phone-input class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" inputmode="numeric" pattern="^(09\d{9}|\+639\d{9})$" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
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
