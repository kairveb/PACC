@extends('layouts.hims')

@section('title', 'Register ER Arrival')

@section('content')
<div class="max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Register ER Arrival</h1>
            <p class="text-sm text-slate-500 mt-1">Emergency and ER Triage System (EERTS)</p>
        </div>
        <a href="{{ route('emergency.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <form method="POST" action="{{ route('emergency.store') }}" class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Patient *</label>
            <select name="patient_id" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                <option value="">Select patient</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->id }}">{{ $patient->full_name }} — {{ $patient->mrn }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Arrival Date/Time</label>
                <input type="datetime-local" name="arrived_at" value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Arrival Method</label>
                <select name="arrival_method" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                    <option value="">Select</option>
                    <option value="Walk-in">Walk-in</option>
                    <option value="Ambulance">Ambulance</option>
                    <option value="Referral">Referral</option>
                    <option value="Police">Police</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Chief Complaint *</label>
            <textarea name="chief_complaint" required rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Referral Details</label>
            <textarea name="referral_details" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></textarea>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700">Register Arrival</button>
            <a href="{{ route('emergency.index') }}" class="px-6 py-2.5 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
