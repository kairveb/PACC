@extends('layouts.hims')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Two-Factor Authentication</h1>
            <p class="mt-1 text-sm text-slate-500">Add an extra layer of security to your hospital account.</p>
        </div>
        <a href="{{ route('profile.edit') }}" class="px-4 py-2 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">Back to Profile</a>
    </div>

    @if (session('status') === 'mfa-enabled')
        <div class="rounded-2xl border border-teal-200 bg-teal-50 p-4 text-sm text-teal-700">Two-factor authentication is now enabled on your account.</div>
    @endif
    @if (session('status') === 'mfa-disabled')
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">Two-factor authentication has been disabled.</div>
    @endif

    @if (auth()->user()->hasMfaEnabled())
        {{-- MFA is enabled --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="rounded-full bg-teal-50 p-3 text-teal-600"><i class="bi bi-shield-check"></i></div>
                <div>
                    <h2 class="font-semibold text-slate-800">MFA is active</h2>
                    <p class="text-sm text-slate-500">Your account is protected with two-factor authentication.</p>
                </div>
            </div>

            <div class="mt-6">
                <h3 class="font-semibold text-slate-800 mb-3">Disable two-factor authentication</h3>
                <p class="text-sm text-slate-600 mb-4">To disable, enter a current authenticator code.</p>
                <form method="POST" action="{{ route('mfa.disable') }}" class="space-y-4">
                    @csrf
                    <div class="max-w-xs">
                        <label for="code" class="block text-sm font-medium text-slate-700 mb-1">Authentication code</label>
                        <input id="code" name="code" type="text" inputmode="numeric" maxlength="6" required
                               class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">Disable MFA</button>
                </form>
            </div>
        </div>
    @else
        {{-- MFA setup --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-slate-800 mb-2">Step 1 · Scan the QR code</h2>
            <p class="text-sm text-slate-600 mb-4">Open your authenticator app (Google Authenticator, Authy, Microsoft Authenticator) and scan this code.</p>

            <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                @if ($qrData)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrData) }}" alt="MFA QR Code" class="h-48 w-48 object-contain">
                    </div>
                    <div class="flex-1 text-sm text-slate-600">
                        <p class="font-medium text-slate-800 mb-1">Or enter the code manually:</p>
                        <code class="block rounded-lg bg-slate-100 px-3 py-2 font-mono text-base tracking-widest text-slate-800 mb-4">{{ $secret }}</code>
                        <p class="text-xs text-slate-500">This secret is shown only once. Register it securely in your authenticator app.</p>
                    </div>
                @else
                    <p class="text-sm text-slate-500">Unable to generate a QR code. Please refresh the page.</p>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-slate-800 mb-2">Step 2 · Confirm your code</h2>
            <p class="text-sm text-slate-600 mb-4">Enter the 6-digit code shown in your authenticator app to enable MFA.</p>
            <form method="POST" action="{{ route('mfa.enable') }}" class="space-y-4">
                @csrf
                <div class="max-w-xs">
                    <label for="confirm_code" class="block text-sm font-medium text-slate-700 mb-1">Authentication code</label>
                    <input id="confirm_code" name="code" type="text" inputmode="numeric" maxlength="6" required
                           class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700">Enable MFA</button>
            </form>
        </div>
    @endif
</div>
@endsection
