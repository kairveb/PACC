@extends('layouts.guest')

@section('title', 'Two-Factor Verification | HIMS')

@section('content')
<div class="space-y-6">
    <div class="space-y-2">
        <p class="text-sm font-medium uppercase tracking-[0.35em] text-teal-600">Secure verification</p>
        <h2 class="text-3xl font-semibold text-slate-900">Two-Factor Authentication</h2>
        <p class="text-sm leading-6 text-slate-600">Enter the 6-digit code from your authenticator app to continue.</p>
    </div>

    @if (session('status'))
        <x-auth-session-status class="mb-4" :status="session('status')" />
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-medium">Please correct the highlighted field below.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('mfa.verify') }}" class="space-y-4" novalidate>
        @csrf

        <div>
            <label for="code" class="mb-2 block text-sm font-medium text-slate-700">Authentication code</label>
            <input id="code" name="code" type="text" inputmode="numeric" maxlength="6" required autofocus autocomplete="one-time-code" placeholder="000000" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-center text-2xl tracking-[0.5em] text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-100" />
            @error('code')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="flex w-full items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
            Verify & Sign In
        </button>
    </form>

    <p class="text-center text-sm text-slate-500">
        Don't have your code? Contact your hospital administrator.
    </p>
</div>
@endsection
