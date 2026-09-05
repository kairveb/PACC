@extends('layouts.hims')

@section('title', 'Register Patient')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-sky-600">Smart Patient Registration</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-800">Register Patient</h1>
        </div>
        <a href="{{ route('patients.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Back to Patients</a>
    </div>

    @include('patients.partials.registration-form')
</div>
@endsection
