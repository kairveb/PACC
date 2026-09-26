@php
    $patient = $patient ?? null;
    $address = $address ?? null;
    $contact = $contact ?? null;

    $firstNameValue = old('first_name', $patient?->first_name ?? '');
    $middleNameValue = old('middle_name', $patient?->middle_name ?? '');
    $lastNameValue = old('last_name', $patient?->last_name ?? '');
    $suffixValue = old('suffix', $patient?->suffix ?? '');
    $dateOfBirthValue = old('date_of_birth', $patient?->date_of_birth?->format('Y-m-d') ?? '');
    $sexValue = old('sex', $patient?->sex ?? '');
    $civilStatusValue = old('civil_status', $patient?->civil_status ?? '');
    $nationalityValue = old('nationality', $patient?->nationality ?? '');
    $phoneValue = old('phone', $patient?->phone ?? '');
    $emailValue = old('email', $patient?->email ?? '');
    $addressLine1Value = old('address_line1', $address?->line1 ?? '');
    $addressProvinceValue = old('address_province', $address?->province ?? '');
    $addressCityValue = old('address_city', $address?->city ?? '');
    $addressBarangayValue = old('address_barangay', $address?->barangay ?? '');
    $addressPostalValue = old('address_postal', $address?->postal_code ?? '');
    $allergiesValue = old('allergies', $patient?->allergies ?? '');
    $emergencyNameValue = old('emergency_name', $contact?->name ?? '');
    $emergencyRelationshipValue = old('emergency_relationship', $contact?->relationship ?? '');
    $emergencyPhoneValue = old('emergency_phone', $contact?->phone ?? '');
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <h3 class="text-lg font-semibold text-slate-900">1. Essential Information</h3>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">First Name *</label>
        <input type="text" name="first_name" value="{{ $firstNameValue }}" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Middle Name</label>
        <input type="text" name="middle_name" value="{{ $middleNameValue }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Last Name *</label>
        <input type="text" name="last_name" value="{{ $lastNameValue }}" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Suffix</label>
        <input type="text" name="suffix" value="{{ $suffixValue }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Date of Birth *</label>
        <input type="date" name="date_of_birth" value="{{ $dateOfBirthValue }}" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Sex *</label>
        <select name="sex" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
            <option value="">Select</option>
            <option value="Male" {{ $sexValue === 'Male' ? 'selected' : '' }}>Male</option>
            <option value="Female" {{ $sexValue === 'Female' ? 'selected' : '' }}>Female</option>
            <option value="Other" {{ $sexValue === 'Other' ? 'selected' : '' }}>Other</option>
        </select>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Civil Status</label>
        <input type="text" name="civil_status" value="{{ $civilStatusValue }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Nationality</label>
        <input type="text" name="nationality" value="{{ $nationalityValue }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Contact Number</label>
        <input type="tel" name="phone" value="{{ $phoneValue }}" data-phone-input class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" inputmode="numeric" pattern="^(09\d{9}|\+639\d{9})$" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
        <input type="email" name="email" value="{{ $emailValue }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
    </div>

    <div class="md:col-span-2">
        <h3 class="text-lg font-semibold text-slate-900">2. Address</h3>
    </div>

    <div class="md:col-span-2">
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Street Address</label>
        <input type="text" name="address_line1" value="{{ $addressLine1Value }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="House number, street, subdivision">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Province</label>
        <input type="text" name="address_province" value="{{ $addressProvinceValue }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">City / Municipality</label>
        <input type="text" name="address_city" value="{{ $addressCityValue }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
    </div>

    <div class="md:col-span-2">
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Barangay</label>
        <input type="text" name="address_barangay" value="{{ $addressBarangayValue }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
    </div>

    <div class="md:col-span-2">
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Postal Code</label>
        <input type="text" name="address_postal" value="{{ $addressPostalValue }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
    </div>

    <div class="md:col-span-2">
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Allergies / Alerts</label>
        <textarea name="allergies" rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">{{ $allergiesValue }}</textarea>
    </div>

    <div class="md:col-span-2">
        <h3 class="text-lg font-semibold text-slate-900">Emergency Contact</h3>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Emergency Contact Name</label>
        <input type="text" name="emergency_name" value="{{ $emergencyNameValue }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Emergency Contact Relationship</label>
        <input type="text" name="emergency_relationship" value="{{ $emergencyRelationshipValue }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
    </div>

    <div class="md:col-span-2">
        <label class="mb-1.5 block text-sm font-medium text-slate-700">Emergency Contact Phone</label>
        <input type="tel" name="emergency_phone" value="{{ $emergencyPhoneValue }}" data-phone-input class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" inputmode="numeric" pattern="^(09\d{9}|\+639\d{9})$" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
    </div>
</div>
