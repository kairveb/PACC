@extends('layouts.hims')

@section('title', 'Register Patient')

@section('content')
<div class="max-w-4xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Register Patient</h1>
        <p class="text-sm text-slate-500 mt-1">Smart Patient Registration System (SPRS)</p>
    </div>

    <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4" x-data="{
        q: '',
        results: [],
        async search() {
            if (!this.q.trim()) { this.results = []; return; }
            const url = `{{ route('patients.lookup') }}?q=${encodeURIComponent(this.q)}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            this.results = data.data || [];
        },
        fill(item) {
            const map = {
                first_name: item.first_name || '',
                middle_name: item.middle_name || '',
                last_name: item.last_name || '',
                date_of_birth: item.date_of_birth || '',
                sex: item.sex || '',
                phone: item.phone || '',
                email: item.email || '',
                address_line1: item.address?.line1 || '',
                address_city: item.address?.city || '',
                address_barangay: item.address?.barangay || '',
                address_province: item.address?.province || '',
                address_postal: item.address?.postal_code || '',
                emergency_name: item.emergency_contact?.name || '',
                emergency_relationship: item.emergency_contact?.relationship || '',
                emergency_phone: item.emergency_contact?.phone || '',
            };

            Object.entries(map).forEach(([name, value]) => {
                const el = document.querySelector(`[name='${name}']`);
                if (el) el.value = value;
            });

            this.q = '';
            this.results = [];
        }
    }">
        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-800">Fast lookup: pre-registered patient</h2>
            <span class="text-xs font-medium uppercase tracking-[0.2em] text-sky-700">pending arrival</span>
        </div>

        <input
            type="text"
            x-model="q"
            @input.debounce.300ms="search()"
            placeholder="Search by patient name, email, phone, or reference code"
            class="w-full rounded-lg border border-sky-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
        >

        <div x-show="results.length" class="mt-3 space-y-2">
            <template x-for="item in results" :key="item.id">
                <button type="button"
                        @click="fill(item)"
                        class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white p-3 text-left hover:border-sky-400">
                    <div>
                        <div class="font-medium text-slate-900" x-text="`${item.first_name} ${item.last_name}`"></div>
                        <div class="text-xs text-slate-500" x-text="`Ref: ${item.lookup_code || '—'} · DOB: ${item.date_of_birth || '—'}`"></div>
                    </div>
                    <span class="text-xs font-semibold uppercase text-sky-700">Load</span>
                </button>
            </template>
        </div>
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
        </div>

        <div class="mt-4">
            <x-philippine-address-fields
                :province-name="'address_province'"
                :city-name="'address_city'"
                :barangay-name="'address_barangay'"
                :province-value="old('address_province')"
                :city-value="old('address_city')"
                :barangay-value="old('address_barangay')"
            />
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
