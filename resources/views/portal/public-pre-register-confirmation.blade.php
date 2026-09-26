@extends('layouts.guest')

@section('title', 'Pre-registration Submitted')
@section('page-kicker', 'Patient access')
@section('page-title', 'Pre-registration Submitted')
@section('page-badge', 'Save your reference')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="panel-card p-6 text-center">
        <h2 class="text-2xl font-semibold text-slate-900">Your pre-registration is ready</h2>
        <p class="mt-2 text-sm text-slate-600">Show this reference code to staff when you arrive. Keep it available for check-in.</p>
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-6">
            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Reference code</div>
            <div class="mt-2 text-4xl font-black tracking-[0.2em] text-emerald-800">{{ $profile->reference_code }}</div>
        </div>
        <a href="{{ url('/') }}" class="mt-6 inline-flex rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700">Done</a>
    </div>
</div>
@endsection
