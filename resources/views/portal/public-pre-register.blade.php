@extends('layouts.guest')

@section('title', 'Pre-register for Care')
@section('page-kicker', 'Patient access')
@section('page-title', 'Pre-register for Care')
@section('page-badge', 'No account required')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="panel-card p-6">
        <h2 class="text-2xl font-semibold text-slate-900">Pre-register before arrival</h2>
        <p class="mt-1 text-sm text-slate-600">Submit your patient and visit information before coming to the hospital. Staff will review it at arrival.</p>
    </div>

    <form method="POST" action="{{ route('public.pre-register.store') }}" class="panel-card space-y-6 p-6">
        @csrf
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2"><h3 class="text-lg font-semibold text-slate-900">1. Essential Information</h3></div>
            <div><label class="mb-1.5 block text-sm font-medium text-slate-700">First Name *</label><input type="text" name="first_name" value="{{ old('first_name') }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5"></div>
            <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Middle Name</label><input type="text" name="middle_name" value="{{ old('middle_name') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5"></div>
            <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Last Name *</label><input type="text" name="last_name" value="{{ old('last_name') }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5"></div>
            <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Suffix</label><input type="text" name="suffix" value="{{ old('suffix') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5"></div>
            <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Date of Birth *</label><input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5"></div>
            <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Sex *</label><select name="sex" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5"><option value="">Select</option><option value="Male" {{ old('sex') === 'Male' ? 'selected' : '' }}>Male</option><option value="Female" {{ old('sex') === 'Female' ? 'selected' : '' }}>Female</option><option value="Other" {{ old('sex') === 'Other' ? 'selected' : '' }}>Other</option></select></div>
            <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Civil Status</label><input type="text" name="civil_status" value="{{ old('civil_status') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5"></div>
            <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Nationality</label><input type="text" name="nationality" value="{{ old('nationality') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5"></div>
            <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Contact Number</label><input type="tel" name="phone" value="{{ old('phone') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5" inputmode="numeric" pattern="^(09\d{9}|\+639\d{9})$" placeholder="09XXXXXXXXX or +639XXXXXXXXX"></div>
            <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label><input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5"></div>
            <div class="md:col-span-2"><h3 class="text-lg font-semibold text-slate-900">2. Address</h3></div>
            <div class="md:col-span-2"><label class="mb-1.5 block text-sm font-medium text-slate-700">Street Address</label><input type="text" name="address_line1" value="{{ old('address_line1') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5"></div>
            <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Province</label><input type="text" name="address_province" value="{{ old('address_province') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5"></div>
            <div><label class="mb-1.5 block text-sm font-medium text-slate-700">City / Municipality</label><input type="text" name="address_city" value="{{ old('address_city') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5"></div>
            <div class="md:col-span-2"><label class="mb-1.5 block text-sm font-medium text-slate-700">Barangay</label><input type="text" name="address_barangay" value="{{ old('address_barangay') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5"></div>
            <div class="md:col-span-2"><label class="mb-1.5 block text-sm font-medium text-slate-700">Postal Code</label><input type="text" name="address_postal" value="{{ old('address_postal') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5"></div>
            <div class="md:col-span-2"><h3 class="text-lg font-semibold text-slate-900">Emergency Contact</h3></div>
            <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Emergency Contact Name</label><input type="text" name="emergency_name" value="{{ old('emergency_name') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5"></div>
            <div><label class="mb-1.5 block text-sm font-medium text-slate-700">Emergency Contact Relationship</label><input type="text" name="emergency_relationship" value="{{ old('emergency_relationship') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5"></div>
            <div class="md:col-span-2"><label class="mb-1.5 block text-sm font-medium text-slate-700">Emergency Contact Phone</label><input type="tel" name="emergency_phone" value="{{ old('emergency_phone') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5" inputmode="numeric" pattern="^(09\d{9}|\+639\d{9})$" placeholder="09XXXXXXXXX or +639XXXXXXXXX"></div>
            <div class="md:col-span-2"><label class="mb-1.5 block text-sm font-medium text-slate-700">Allergies / Alerts</label><textarea name="allergies" rows="3" class="w-full rounded-xl border border-slate-300 px-3 py-2.5">{{ old('allergies') }}</textarea></div>
        </div>

        <div class="flex justify-end"><button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Submit pre-registration</button></div>
    </form>
</div>
@endsection
