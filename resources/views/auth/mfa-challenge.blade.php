@extends('layouts.auth')

@section('title', 'Two-Factor Verification | HIMS')
@section('module', 'auth')
@section('page', 'mfa-challenge')

@section('content')
    <main class="login-layout">
        <section class="login-brand" aria-label="HIMS Main System">
            <div class="login-brand-content">
                <div class="login-brand-mark" aria-hidden="true"><i class="ph-fill ph-cross"></i></div>
                <p class="login-kicker">Hospital Information Management System</p>
                <h1>HIMS Main System</h1>
                <p class="login-brand-description">One connected workspace for hospital operations and management modules.</p>
                <ul class="login-brand-signals" aria-label="System trust information">
                    <li><i class="ph ph-shield-check" aria-hidden="true"></i><span>Secure Authentication</span></li>
                    <li><i class="ph ph-identification-card" aria-hidden="true"></i><span>Role-Based Access</span></li>
                    <li><i class="ph ph-buildings" aria-hidden="true"></i><span>Centralized Hospital Operations</span></li>
                </ul>
            </div>
            <footer class="login-brand-footer"><span>HIMS</span><span>Operations</span></footer>
        </section>

        <section class="login-panel" aria-labelledby="mfa-title">
            <div class="login-card">
                <header class="login-card-header">
                    <p class="page-kicker">Secure verification</p>
                    <h2 id="mfa-title">Two-Factor Authentication</h2>
                    <p class="login-help">Enter the 6-digit code from your authenticator app to continue.</p>
                </header>

                @if (session('status'))
                    <x-auth-session-status class="mb-4" :status="session('status')" />
                @endif

                @if ($errors->any())
                    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <p class="font-medium">Please correct the highlighted field below.</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('mfa.verify') }}" id="mfa-form" novalidate>
                    @csrf
                    <div class="form-field">
                        <label for="code">Authentication code</label>
                        <input id="code" name="code" type="text" inputmode="numeric" maxlength="6" required autofocus autocomplete="one-time-code" placeholder="000000" class="text-center tracking-[0.5em]" />
                        @error('code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button class="btn-primary login-submit" type="submit"><i class="ph ph-lock-key-open" aria-hidden="true"></i>Verify &amp; Sign In</button>
                </form>

                <div class="login-support" aria-label="Verification help">
                    <i class="ph ph-question" aria-hidden="true"></i>
                    <p><strong>Need access help?</strong><span>Contact your hospital administrator.</span></p>
                </div>

                <aside class="login-access-notice" aria-label="Security notice">
                    <i class="ph ph-lock-key" aria-hidden="true"></i>
                    <div><strong>Secure access</strong><span>Your session is protected and monitored according to hospital security policies.</span></div>
                </aside>

                <footer class="login-card-footer"><span>Authorized users only</span><span>HIMS</span></footer>
            </div>
        </section>
    </main>
@endsection
