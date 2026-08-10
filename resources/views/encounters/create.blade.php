@extends('layouts.hims')

@section('title', 'New Encounter')

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">New Clinical Encounter</h1>
        <p class="text-sm text-slate-500 mt-1">Telehealth and Outpatient Care System (TOCS)</p>
    </div>

    <form method="POST" action="{{ route('encounters.store') }}" class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Patient *</label>
                <select name="patient_id" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                    <option value="">Select patient</option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}">{{ $patient->full_name }} — {{ $patient->mrn }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Provider *</label>
                <select name="provider_id" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                    <option value="">Select provider</option>
                    @foreach ($providers as $provider)
                        <option value="{{ $provider->id }}">{{ $provider->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Appointment (optional)</label>
                <select name="appointment_id" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                    <option value="">None</option>
                    @foreach ($appointments as $apt)
                        <option value="{{ $apt->id }}">{{ $apt->appointment_number }} — {{ $apt->patient->full_name ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Encounter Type *</label>
                <select name="type" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                    <option value="OUTPATIENT">Outpatient</option>
                    <option value="TELEHEALTH">Telehealth</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Chief Complaint</label>
            <textarea name="chief_complaint" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></textarea>
        </div>

        <div>
            <h4 class="text-sm font-medium text-slate-700 mb-2">Vital Signs</h4>
<div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div><label class="block text-xs text-slate-500 mb-1">BP</label><input name="bp" placeholder="120/80" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Heart Rate</label><input type="number" name="heart_rate" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Resp. Rate</label><input type="number" name="respiratory_rate" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Temp (°C)</label><input type="number" step="0.1" name="temperature" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                <div><label class="block text-xs text-slate-500 mb-1">SpO2</label><input type="number" name="spo2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Clinical Notes</label>
            <textarea name="notes" rows="3" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Assessment</label><textarea name="assessment" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></textarea></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Plan</label><textarea name="plan" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></textarea></div>
        </div>

<div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Follow-up Date</label>
                <input type="date" name="follow_up_date" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 text-sm font-medium bg-teal-600 text-white rounded-lg hover:bg-teal-700">Save Encounter</button>
            <a href="{{ route('encounters.index') }}" class="px-6 py-2.5 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
