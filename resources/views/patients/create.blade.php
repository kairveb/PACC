@extends('layouts.hims')

@section('title', 'Register Patient')

@section('content')
<div class="max-w-4xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Register Patient</h1>
        <p class="text-sm text-slate-500 mt-1">Smart Patient Registration System (SPRS)</p>
    </div>

    @if (session('duplicate_warning'))
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="flex-1">
                    <h3 class="font-semibold text-amber-800">{{ session('duplicate_warning') }}</h3>
                    <div class="mt-3 space-y-2">
                        <strong class="text-sm text-amber-700">Possible matches:</strong>
                        @foreach (session('duplicates') as $dup)
                            <div class="text-sm text-amber-700 bg-white p-2 rounded border border-amber-200">
                                <span class="font-medium">{{ $dup['first_name'] }} {{ $dup['last_name'] }}</span>
                                · {{ $dup['date_of_birth'] ?? '—' }} · <span class="font-mono text-xs">{{ $dup['mrn'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 flex gap-3">
                        <form method="POST" action="{{ route('patients.store') }}" class="inline">
                            @csrf
                            @foreach (request()->all() as $key => $value)
                                @if (is_string($value))
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <button type="submit" class="px-4 py-2 text-sm bg-amber-600 text-white rounded-lg hover:bg-amber-700">Still register as new patient</button>
                        </form>
<a href="{{ route('patients.create') }}" class="px-4 py-2 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">Review again</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('patients.store') }}" class="bg-white rounded-xl border border-slate-200 p-6">
        @csrf

        <h2 class="font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-100">1. Personal Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">First Name *</label>
                <input type="text" name="first_name" value="{{ old('first_name') }}" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Middle Name</label>
                <input type="text" name="middle_name" value="{{ old('middle_name') }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Last Name *</label>
                <input type="text" name="last_name" value="{{ old('last_name') }}" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Suffix</label>
                <input type="text" name="suffix" value="{{ old('suffix') }}" placeholder="Jr., Sr., III" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Date of Birth *</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Sex *</label>
                <select name="sex" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                    <option value="">Select</option>
                    <option value="Male" {{ old('sex') === 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('sex') === 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ old('sex') === 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Civil Status</label>
                <input type="text" name="civil_status" value="{{ old('civil_status') }}" placeholder="Single, Married, etc." class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nationality</label>
                <input type="text" name="nationality" value="{{ old('nationality') }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>
        </div>

        <h2 class="font-semibold text-slate-800 mt-8 mb-4 pb-2 border-b border-slate-100">2. Contact Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Contact Number</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>
        </div>

        <h2 class="font-semibold text-slate-800 mt-8 mb-4 pb-2 border-b border-slate-100">3. Address</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Street Address</label>
                <input type="text" name="address_line1" value="{{ old('address_line1') }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg" placeholder="House number, street, subdivision">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">City</label>
                <div class="relative" x-data="{ query: '{{ old('address_city', '') }}', open: false, options: ['Caloocan','Las Piñas','Makati','Malabon','Mandaluyong','Manila','Marikina','Muntinlupa','Navotas','Parañaque','Pasay','Pasig','Quezon City','San Juan','Taguig','Valenzuela','Baguio','Cebu City','Davao City','Iloilo City','Bacolod','Batangas City','Cagayan de Oro','Dumaguete','General Santos','Lipa','Olongapo','Puerto Princesa','Tacloban','Zamboanga City'], filteredOptions() { const q = this.query.toLowerCase().trim(); if (!q) { return this.options.slice(0, 8); } return this.options.filter(item => item.toLowerCase().includes(q)).slice(0, 8); } }" @click.away="open = false">
                    <input type="text" name="address_city" x-model="query" @focus="open = true" @input="open = true" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100" placeholder="Type a city">
                    <div x-show="open && filteredOptions().length" x-transition class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                        <ul class="max-h-48 overflow-auto py-1">
                            <template x-for="item in filteredOptions()" :key="item">
                                <li>
                                    <button type="button" class="flex w-full items-center px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" @click="query = item; open = false">
                                        <span x-text="item"></span>
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Province</label>
                <div class="relative" x-data="{ query: '{{ old('address_province', '') }}', open: false, options: ['Ilocos Norte','Ilocos Sur','La Union','Pangasinan','Batanes','Cagayan','Isabela','Nueva Vizcaya','Quirino','Bataan','Bulacan','Nueva Ecija','Pampanga','Tarlac','Zambales','Batangas','Cavite','Laguna','Quezon','Rizal','Marinduque','Occidental Mindoro','Oriental Mindoro','Palawan','Romblon','Albay','Camarines Norte','Camarines Sur','Catanduanes','Masbate','Sorsogon','Aklan','Antique','Capiz','Guimaras','Iloilo','Negros Occidental','Negros Oriental','Bohol','Cebu','Eastern Samar','Leyte','Northern Samar','Samar','Southern Leyte','Biliran','Zamboanga del Norte','Zamboanga del Sur','Zamboanga Sibugay','Bukidnon','Camiguin','Lanao del Norte','Misamis Occidental','Misamis Oriental','Davao de Oro','Davao del Norte','Davao del Sur','Davao Occidental','Davao Oriental','Cotabato','Sarangani','South Cotabato','Sultan Kudarat','North Cotabato','Basilan','Lanao del Sur','Maguindanao','Sulu','Tawi-Tawi','Abra','Apayao','Benguet','Ifugao','Kalinga','Mountain Province','Metro Manila'], filteredOptions() { const q = this.query.toLowerCase().trim(); if (!q) { return this.options.slice(0, 8); } return this.options.filter(item => item.toLowerCase().includes(q)).slice(0, 8); } }" @click.away="open = false">
                    <input type="text" name="address_province" x-model="query" @focus="open = true" @input="open = true" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100" placeholder="Type a province">
                    <div x-show="open && filteredOptions().length" x-transition class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                        <ul class="max-h-48 overflow-auto py-1">
                            <template x-for="item in filteredOptions()" :key="item">
                                <li>
                                    <button type="button" class="flex w-full items-center px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" @click="query = item; open = false">
                                        <span x-text="item"></span>
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="font-semibold text-slate-800 mt-8 mb-4 pb-2 border-b border-slate-100">4. Emergency Contact</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                <input type="text" name="emergency_name" value="{{ old('emergency_name') }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Relationship</label>
                <input type="text" name="emergency_relationship" value="{{ old('emergency_relationship') }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                <input type="text" name="emergency_phone" value="{{ old('emergency_phone') }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>
        </div>

        <h2 class="font-semibold text-slate-800 mt-8 mb-4 pb-2 border-b border-slate-100">5. Medical Alerts</h2>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Allergies / Alerts</label>
            <textarea name="allergies" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">{{ old('allergies') }}</textarea>
        </div>

        <div class="mt-8 flex items-center gap-3">
            <button type="submit" class="px-6 py-2.5 text-sm font-medium bg-teal-600 text-white rounded-lg hover:bg-teal-700">Register Patient</button>
            <a href="{{ route('patients.index') }}" class="px-6 py-2.5 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
