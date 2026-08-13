@extends('layouts.hims')

@section('title', 'My Patient Profile')

@section('content')
<div class="max-w-5xl space-y-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-600">Patient portal</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">Pre-registration profile</h1>
        </div>

        @if ($patient->lookup_code)
            <div class="rounded-xl border border-teal-200 bg-teal-50 px-3 py-2 text-sm font-semibold text-teal-700">
                Reference: {{ $patient->lookup_code }}
            </div>
        @endif
    </div>

    <form method="POST" action="{{ route('patients.profile.save') }}" class="space-y-6">
        @csrf

        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Personal details</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">First name</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $patient->first_name) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Middle name</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name', $patient->middle_name) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Last name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $patient->last_name) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Date of birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($patient->date_of_birth)->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Sex</label>
                    <select name="sex" class="w-full rounded-lg border border-slate-300 px-3 py-2" required>
                        <option value="">Select</option>
                        <option value="Male" {{ old('sex', $patient->sex) === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('sex', $patient->sex) === 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('sex', $patient->sex) === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $patient->phone) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $patient->email ?? auth()->user()->email) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Civil status</label>
                    <input type="text" name="civil_status" value="{{ old('civil_status', $patient->civil_status) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality', $patient->nationality) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Address</h2>

            @php
                $address = $patient->addresses->first();
            @endphp

            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Street address</label>
                    <input type="text" name="address[line1]" value="{{ old('address.line1', $address->line1 ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>

                <x-philippine-address-fields
                    :province-name="'address[province]'"
                    :city-name="'address[city]'"
                    :barangay-name="'address[barangay]'"
                    :province-value="old('address.province', $address->province ?? '')"
                    :city-value="old('address.city', $address->city ?? '')"
                    :barangay-value="old('address.barangay', $address->barangay ?? '')"
                />

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Postal code</label>
                    <input type="text" name="address[postal_code]" value="{{ old('address.postal_code', $address->postal_code ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Emergency contact</h2>

            @php
                $contact = $patient->emergencyContacts->first();
            @endphp

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" name="emergency_contact[name]" value="{{ old('emergency_contact.name', $contact->name ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Relationship</label>
                    <input type="text" name="emergency_contact[relationship]" value="{{ old('emergency_contact.relationship', $contact->relationship ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Phone</label>
                    <input type="text" name="emergency_contact[phone]" value="{{ old('emergency_contact.phone', $contact->phone ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Medical alerts</h2>
            <textarea name="allergies" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('allergies', $patient->allergies) }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('dashboard') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">Cancel</a>
            <button type="submit" class="rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">
                Save pre-registration details
            </button>
        </div>
    </form>
</div>
@endsection
