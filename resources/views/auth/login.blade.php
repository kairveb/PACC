@extends('layouts.auth')

@section('title', 'Sign in | HIMS')
@section('module', 'auth')
@section('page', 'login')

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

        <section class="login-panel" aria-labelledby="login-title">
            <div class="login-card">
                <header class="login-card-header">
                    <p class="page-kicker">Welcome back</p>
                    <h2 id="login-title">Sign in to HIMS</h2>
                    <p class="login-help">Use your hospital access profile to continue to the operations workspace.</p>
                </header>

                <form method="POST" action="{{ route('login') }}" id="login-form" novalidate>
                    @csrf
                    <div class="form-field">
                        <label for="login-email">Email address</label>
                        <input id="login-email" name="email" type="email" autocomplete="email" placeholder="name@hospital.org" required value="{{ old('email') }}">
                        @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-field">
                        <label for="login-password">Password</label>
                        <div class="password-field">
                            <input id="login-password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" required aria-describedby="password-note">
                            <button class="password-toggle" type="button" data-password-toggle aria-label="Show password" aria-pressed="false"><i class="ph ph-eye" aria-hidden="true"></i></button>
                        </div>
                        <small id="password-note">Use the password assigned to your hospital account.</small>
                        @error('password')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <label class="remember-field"><input id="remember-email" name="remember_email" type="checkbox">Remember email on this device</label>
                    <p class="form-error" id="login-error" role="alert" hidden></p>
                    <button class="btn-primary login-submit" type="submit"><i class="ph ph-sign-in" aria-hidden="true"></i>Sign in</button>
                </form>

                <div class="login-support" aria-label="Sign-in help">
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

@push('scripts')
    <script src="{{ asset('assets/js/auth/login.js') }}"></script>
@endpush
