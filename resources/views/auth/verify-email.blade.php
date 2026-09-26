@extends('layouts.auth')

@section('title', 'Verify email | HIMS')
@section('module', 'auth')
@section('page', 'verify-email')

@section('content')
    <main class="login-layout">
        <section class="login-brand" aria-label="HIMS Main System">
            <div class="login-brand-content">
                <div class="login-brand-mark" aria-hidden="true">
                    <span class="logo-icon" aria-hidden="true"><i class="ph-fill ph-cross"></i></span>
                </div>
                <p class="login-kicker">Hospital Information Management System</p>
                <h1>HIMS Main System</h1>
                <p class="login-brand-description">One connected care environment for patients, staff, and community health services.</p>
                <ul class="login-brand-signals" aria-label="System trust information">
                    <li><i class="ph ph-shield-check" aria-hidden="true"></i><span>Secure Care Access</span></li>
                    <li><i class="ph ph-identification-card" aria-hidden="true"></i><span>Trusted Team Access</span></li>
                    <li><i class="ph ph-buildings" aria-hidden="true"></i><span>Coordinated Community Care</span></li>
                </ul>
            </div>
            <footer class="login-brand-footer"><span>HIMS</span><span>Care Services</span></footer>
        </section>

        <section class="login-panel" aria-labelledby="verify-email-title">
            <div class="login-card">
                <header class="login-card-header">
                    <p class="page-kicker">Verify email</p>
                    <h2 id="verify-email-title">Verify Your Email Address</h2>
                    <p class="login-help">{{ __('Thanks for signing up for HIMS! Before getting started, could you verify your email address by clicking the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}</p>
                </header>

                @if (session('status') == 'verification-link-sent')
                    <div class="login-status success" role="status">
                        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                    </div>
                @endif

                <div style="display: flex; flex-direction: column; gap: 0.9rem; margin-top: 1.25rem;">
                    <form method="POST" action="{{ route('verification.send') }}" class="verify-email-form" style="margin: 0; width: 100%;">
                        @csrf
                        <button class="btn-primary login-submit" type="submit" style="width: 100%;"><i class="ph ph-paper-plane-tilt" aria-hidden="true"></i>{{ __('Resend Verification Email') }}</button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="logout-form" style="margin: 0; width: 100%;">
                        @csrf
                        <button type="submit" class="text-button login-logout-link" style="width: 100%; justify-content: center; background: #e2e8f0; color: #0f172a; border: 1px solid #cbd5e1; padding: 0.8rem 1rem; border-radius: 1rem; font-weight: 600;">{{ __('Log Out') }}</button>
                    </form>
                </div>

                <div class="login-support" aria-label="Account support">
                    <i class="ph ph-question" aria-hidden="true"></i>
                    <p><strong>Need help?</strong><span>Please contact your hospital administrator if you are having trouble receiving the verification email.</span></p>
                </div>

                <aside class="login-access-notice" aria-label="Security notice">
                    <i class="ph ph-lock-key" aria-hidden="true"></i>
                    <div><strong>Secure access</strong><span>Your account is protected to help keep patient information private and accessible only to authorized users.</span></div>
                </aside>

                <footer class="login-card-footer"><span>Authorized users only</span><span>HIMS</span></footer>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/auth/login.js') }}"></script>
@endpush
