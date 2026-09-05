<form method="POST" action="{{ route('appointments.store') }}" class="space-y-4">
    @csrf

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Patient *</label>
        <select name="patient_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
            <option value="">Select patient</option>
            @foreach ($patients as $patient)
                <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>{{ $patient->full_name }} — {{ $patient->mrn }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Department *</label>
            <select name="department_id" id="department" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                <option value="">Select department</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Appointment Type *</label>
            <select name="appointment_type_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                @foreach ($appointmentTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Provider *</label>
        <select name="provider_id" id="provider" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
            <option value="">Select provider</option>
            @foreach ($providers as $provider)
                <option value="{{ $provider->id }}">{{ $provider->full_name }} — {{ $provider->department->name ?? 'General' }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Appointment Date *</label>
        <input type="date" name="appointment_date" id="appointment_date" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
    </div>

    <div id="slots-container">
        {{-- Available slots loaded via AJAX --}}
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Reason / Notes</label>
        <textarea name="reason" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-100">{{ old('reason') }}</textarea>
    </div>

    <div class="flex items-center justify-end gap-3 pt-2">
        <button type="button" class="rounded-xl border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">Book Appointment</button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const providerSelect = document.getElementById('provider');
    const dateInput = document.getElementById('appointment_date');
    const slotsContainer = document.getElementById('slots-container');

    if (!providerSelect || !dateInput || !slotsContainer) {
        return;
    }

    function loadSlots() {
        const providerId = providerSelect.value;
        const date = dateInput.value;
        if (!providerId || !date) {
            slotsContainer.innerHTML = '';
            return;
        }

        fetch(`/appointments/slots/json?provider_id=${providerId}&date=${date}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.data || data.data.length === 0) {
                slotsContainer.innerHTML = '<p class="mt-2 text-sm text-slate-400">No available slots for this provider on the selected date.</p>';
                return;
            }

            let html = '<label class="mb-2 block text-sm font-medium text-slate-700">Available Slots</label><div class="grid grid-cols-3 gap-2 md:grid-cols-4">';
            data.data.forEach(slot => {
                const time = new Date(slot.starts_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                html += `
                    <label class="cursor-pointer rounded-lg border border-slate-200 p-2 text-center text-sm hover:border-teal-500">
                        <input type="radio" name="starts_at" value="${slot.starts_at}" class="sr-only peer">
                        <span class="peer-checked:font-semibold peer-checked:text-teal-600">${time}</span>
                    </label>
                `;
            });
            html += '</div>';
            slotsContainer.innerHTML = html;
        })
        .catch(() => {
            slotsContainer.innerHTML = '<p class="mt-2 text-sm text-red-500">Could not load slots.</p>';
        });
    }

    providerSelect.addEventListener('change', loadSlots);
    dateInput.addEventListener('change', loadSlots);
});
</script>
@endpush
