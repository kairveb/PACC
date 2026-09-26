<div class="space-y-6">
    <script>
        function patientLookupState() {
            const lookupUrl = @json(route('patients.lookup'));

            return {
                q: '',
                results: [],
                searchError: '',
                async search() {
                    if (!this.q.trim()) {
                        this.results = [];
                        this.searchError = '';
                        return;
                    }

                    const url = lookupUrl + '?q=' + encodeURIComponent(this.q);

                    try {
                        const res = await fetch(url, { headers: { Accept: 'application/json' } });

                        if (!res.ok) {
                            this.results = [];
                            this.searchError = res.status === 403
                                ? 'You do not have permission to search patients.'
                                : 'Unable to search right now. Please try again.';
                            return;
                        }

                        const data = await res.json();
                        this.results = data.data || [];
                        this.searchError = '';
                    } catch (e) {
                        this.results = [];
                        this.searchError = 'Unable to search right now. Please try again.';
                    }
                },
                fill(item) {
                    const form = document.querySelector('#registerPatientModal form[action*=\'patients\']');
                    const map = {
                        first_name: item.first_name || '',
                        middle_name: item.middle_name || '',
                        last_name: item.last_name || '',
                        suffix: item.suffix || '',
                        date_of_birth: item.date_of_birth || '',
                        sex: item.sex || '',
                        civil_status: item.civil_status || '',
                        nationality: item.nationality || '',
                        phone: item.phone || '',
                        email: item.email || '',
                        allergies: item.allergies || '',
                        address_line1: item.address && item.address.line1 ? item.address.line1 : '',
                        address_city: item.address && item.address.city ? item.address.city : '',
                        address_barangay: item.address && item.address.barangay ? item.address.barangay : '',
                        address_province: item.address && item.address.province ? item.address.province : '',
                        address_postal: item.address && item.address.postal_code ? item.address.postal_code : '',
                        emergency_name: item.emergency_contact && item.emergency_contact.name ? item.emergency_contact.name : '',
                        emergency_relationship: item.emergency_contact && item.emergency_contact.relationship ? item.emergency_contact.relationship : '',
                        emergency_phone: item.emergency_contact && item.emergency_contact.phone ? item.emergency_contact.phone : '',
                    };

                    Object.entries(map).forEach(([name, value]) => {
                        const target = form || document;
                        const candidates = target.querySelectorAll('[name="' + name + '"]');

                        if (!candidates.length) {
                            return;
                        }

                        candidates.forEach((el) => {
                            if (el.tagName === 'SELECT') {
                                const option = Array.from(el.options).find((opt) => opt.value === String(value));
                                el.value = option ? String(value) : '';
                                return;
                            }

                            el.value = value || '';
                        });
                    });

                    this.q = '';
                    this.results = [];
                }
            };
        }
    </script>
    <div class="space-y-3" x-data="patientLookupState()">
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

        <p x-show="searchError" x-text="searchError" class="mt-2 text-sm text-rose-600"></p>

        <div x-show="results.length" class="mt-3 space-y-2">
            <template x-for="item in results" :key="item.id">
                <button type="button"
                        @click="fill(item)"
                        class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white p-3 text-left hover:border-sky-400">
                    <div>
                        <div class="font-medium text-slate-900" x-text="`${item.first_name} ${item.last_name}`"></div>
                        <div class="text-xs text-slate-500" x-text="item.reference_code ? `Pre-arrival ref: ${item.reference_code} · DOB: ${item.date_of_birth || '—'}` : `Patient lookup code: ${item.lookup_code || '—'} · DOB: ${item.date_of_birth || '—'}`"></div>
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

        @include('portal.partials.pre-registration-fields', [
            'patient' => null,
            'address' => null,
            'contact' => null,
        ])

        <div class="mt-8 flex items-center justify-end gap-3">
            <button type="button" class="rounded-xl border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">Register Patient</button>
        </div>
    </form>
</div>
