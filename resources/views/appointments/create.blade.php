@extends('layouts.hims')

@section('title', 'Book Appointment')

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Book Appointment</h1>
            <p class="text-sm text-slate-500 mt-1">Select a provider and available time slot</p>
        </div>
        <a href="{{ route('appointments.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <form method="POST" action="{{ route('appointments.store') }}" class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Patient *</label>
            <select name="patient_id" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                <option value="">Select patient</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>{{ $patient->full_name }} — {{ $patient->mrn }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Department *</label>
                <select name="department_id" id="department" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                    <option value="">Select department</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Appointment Type *</label>
                <select name="appointment_type_id" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                    @foreach ($appointmentTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Provider *</label>
            <select name="provider_id" id="provider" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                <option value="">Select provider</option>
                @foreach ($providers as $provider)
                    <option value="{{ $provider->id }}">{{ $provider->full_name }} — {{ $provider->department->name ?? 'General' }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Appointment Date *</label>
            <input type="date" name="appointment_date" id="appointment_date" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
        </div>

        <div id="slots-container">
            {{-- Available slots loaded via AJAX --}}
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Reason / Notes</label>
            <textarea name="reason" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">{{ old('reason') }}</textarea>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 text-sm font-medium bg-teal-600 text-white rounded-lg hover:bg-teal-700">Book Appointment</button>
            <a href="{{ route('appointments.index') }}" class="px-6 py-2.5 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const providerSelect = document.getElementById('provider');
    const dateInput = document.getElementById('appointment_date');
    const slotsContainer = document.getElementById('slots-container');

    function loadSlots() {
        const providerId = providerSelect.value;
        const date = dateInput.value;
        if (!providerId || !date) { slotsContainer.innerHTML = ''; return; }

        fetch(`/appointments/slots/json?provider_id=${providerId}&date=${date}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.data || data.data.length === 0) {
                slotsContainer.innerHTML = '<p class="text-sm text-slate-400 mt-2">No available slots for this provider on the selected date.</p>';
                return;
            }
            let html = '<label class="block text-sm font-medium text-slate-700 mb-2">Available Slots</label><div class="grid grid-cols-3 md:grid-cols-4 gap-2">';
            data.data.forEach(slot => {
                const time = new Date(slot.starts_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                html += `<label class="cursor-pointer border border-slate-200 rounded-lg p-2 text-center text-sm hover:border-teal-500">
                            <input type="radio" name="starts_at" value="${slot.starts_at}" class="sr-only peer">
                            <span class="peer-checked:text-teal-600 peer-checked:font-semibold">${time}</span>
                        </label>`;
            });
            html += '</div>';
            slotsContainer.innerHTML = html;
        })
        .catch(() => { slotsContainer.innerHTML = '<p class="text-sm text-red-500 mt-2">Could not load slots.</p>'; });
    }

    providerSelect.addEventListener('change', loadSlots);
    dateInput.addEventListener('change', loadSlots);
});
</script>
@endpush
