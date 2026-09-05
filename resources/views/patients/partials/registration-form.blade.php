<div class="space-y-6">
    <div class="space-y-3" x-data="{
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
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-2">
            <h2 class="text-lg font-semibold text-slate-800">Fast lookup: pre-registered patient</h2>
            <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-sky-700">pending arrival</span>
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
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="flex-1">
                    <h3 class="font-semibold text-amber-800">{{ session('duplicate_warning') }}</h3>
                    <div class="mt-3 space-y-2">
                        <strong class="text-sm text-amber-700">Possible matches:</strong>
                        @foreach (session('duplicates') as $dup)
                            <div class="rounded border border-amber-200 bg-white p-2 text-sm text-amber-700">
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
                            <button type="submit" class="rounded-lg bg-amber-600 px-4 py-2 text-sm text-white hover:bg-amber-700">Still register as new patient</button>
                        </form>
                        <a href="{{ route('patients.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Review again</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('patients.store') }}" class="space-y-6">
        @csrf

        <h2 class="border-b border-slate-200 pb-2 text-lg font-semibold text-slate-800">1. Essential Information</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">First Name *</label>
                <input type="text" name="first_name" value="{{ old('first_name') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Last Name *</label>
                <input type="text" name="last_name" value="{{ old('last_name') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Date of Birth *</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Sex *</label>
                <select name="sex" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                    <option value="">Select</option>
                    <option value="Male" {{ old('sex') === 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('sex') === 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ old('sex') === 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Contact Number</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" data-phone-input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100" inputmode="numeric" pattern="^(09\d{9}|\+639\d{9})$" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
            </div>
        </div>

        <h2 class="mt-8 mb-4 border-b border-slate-100 pb-2 text-lg font-semibold text-slate-800">2. Address</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">Street Address</label>
                <input type="text" name="address_line1" value="{{ old('address_line1') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100" placeholder="House number, street, subdivision">
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

        <h2 class="mt-8 mb-4 border-b border-slate-100 pb-2 text-lg font-semibold text-slate-800">3. Emergency Contact</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Emergency Contact Name</label>
                <input type="text" name="emergency_name" value="{{ old('emergency_name') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Emergency Contact Phone</label>
                <input type="tel" name="emergency_phone" value="{{ old('emergency_phone') }}" data-phone-input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100" inputmode="numeric" pattern="^(09\d{9}|\+639\d{9})$" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
            </div>
        </div>

        <div class="mt-8">
            <details class="rounded-xl border border-slate-200 bg-slate-50">
                <summary class="cursor-pointer list-none p-4 text-sm font-semibold text-slate-700">
                    Advanced Details
                </summary>
                <div class="border-t border-slate-200 p-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Suffix</label>
                            <input type="text" name="suffix" value="{{ old('suffix') }}" placeholder="Jr., Sr., III" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Civil Status</label>
                            <input type="text" name="civil_status" value="{{ old('civil_status') }}" placeholder="Single, Married, etc." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nationality</label>
                            <input type="text" name="nationality" value="{{ old('nationality') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Emergency Contact Relationship</label>
                            <input type="text" name="emergency_relationship" value="{{ old('emergency_relationship') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Allergies / Alerts</label>
                            <textarea name="allergies" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">{{ old('allergies') }}</textarea>
                        </div>
                    </div>
                </div>
            </details>
        </div>

        <div class="mt-8 flex items-center justify-end gap-3">
            <button type="button" class="rounded-xl border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">Register Patient</button>
        </div>
    </form>
</div>
