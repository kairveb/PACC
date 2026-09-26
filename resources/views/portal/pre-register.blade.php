@extends('layouts.hims')

@section('title', 'Pre-registration')
@section('page-kicker', 'Patient portal')
@section('page-title', 'Pre-registration')
@section('page-badge', 'Secure arrival')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="panel-card p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Pre-registration</h2>
                <p class="mt-1 text-sm text-slate-600">Provide your arrival information before your visit so staff can prepare your intake quickly and accurately.</p>
            </div>
            <a href="{{ route('patients.portal') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Back to dashboard</a>
        </div>
    </div>

    <form method="POST" action="{{ route('portal.pre-register.store') }}" class="space-y-6">
        @csrf

        <div class="panel-card p-6">
            <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-800">
                This form is for your pre-arrival information only. Clinical or vital-sign fields are intentionally left blank and will be completed by staff during intake.
            </div>

            @include('portal.partials.pre-registration-fields', [
                'patient' => $patient,
                'address' => $address,
                'contact' => $contact,
            ])

            <div class="mt-6 flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                <a href="{{ route('patients.portal') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">Save pre-registration</button>
            </div>
        </div>
    </form>
</div>
@endsection
