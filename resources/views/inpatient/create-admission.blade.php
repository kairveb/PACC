@extends('layouts.hims')

@section('title', 'New Admission')

@section('content')
<div class="max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">New Admission</h1>
        <p class="text-sm text-slate-500 mt-1">Inpatient and Bed Management System (IBMS)</p>
    </div>

    <form method="POST" action="{{ route('admissions.store') }}" class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
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
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Attending Provider</label>
            <select name="attending_provider_id" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                <option value="">Select provider</option>
                @foreach ($providers as $provider)
                    <option value="{{ $provider->id }}">{{ $provider->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Reason for Admission</label>
            <textarea name="reason" rows="3" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></textarea>
        </div>
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 text-sm font-medium bg-teal-600 text-white rounded-lg hover:bg-teal-700">Create Admission Request</button>
            <a href="{{ route('admissions.index') }}" class="px-6 py-2.5 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
