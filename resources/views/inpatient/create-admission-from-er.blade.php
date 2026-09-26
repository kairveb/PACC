@extends('layouts.hims')

@section('title', 'Convert ER Visit to Admission')

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">ER to inpatient</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-800">Convert ER Visit to Admission</h1>
        <p class="mt-1 text-sm text-slate-500">Patient: <span class="font-medium text-slate-700">{{ $visit->patient->full_name ?? '—' }}</span> · {{ $visit->patient->mrn ?? '' }}</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('emergency.admission.store', $visit) }}" class="space-y-5">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Chief complaint</label>
                    <input type="text" value="{{ old('reason', $visit->chief_complaint) }}" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-700" disabled>
                </div>

                <div>
                    <label for="attending_provider_id" class="mb-1.5 block text-sm font-medium text-slate-700">Attending provider</label>
                    <select id="attending_provider_id" name="attending_provider_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800">
                        <option value="">Select provider</option>
                        @foreach ($providers as $provider)
                            <option value="{{ $provider->id }}" {{ old('attending_provider_id') == $provider->id ? 'selected' : '' }}>
                                {{ $provider->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="reason" class="mb-1.5 block text-sm font-medium text-slate-700">Admission reason</label>
                    <input id="reason" name="reason" type="text" value="{{ old('reason', $visit->chief_complaint) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800" placeholder="Observation, inpatient evaluation...">
                </div>
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                    Create Admission
                </button>
                <a href="{{ route('emergency.show', $visit) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
