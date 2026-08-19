@extends('layouts.auth')

@section('title', 'Create account | HIMS')
@section('module', 'auth')
@section('page', 'register')

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

        <section class="login-panel" aria-labelledby="register-title">
            <div class="login-card">
                <header class="login-card-header">
                    <p class="page-kicker">Create account</p>
                    <h2 id="register-title">Set up your HIMS profile</h2>
                    <p class="login-help">Register for access to the patient access and care coordination system.</p>
                </header>

                <form method="POST" action="{{ route('register') }}" id="register-form" novalidate>
                    @csrf
                    <div class="form-field">
                        <label for="name">Name</label>
                        <input id="name" name="name" type="text" autocomplete="name" placeholder="Your full name" value="{{ old('name') }}" required autofocus>
                        @error('name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-field">
                        <label for="email">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="username" placeholder="name@hospital.org" value="{{ old('email') }}" required>
                        @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-field">
                        <label for="password">Password</label>
                        <div class="password-field">
                            <input id="password" name="password" type="password" autocomplete="new-password" placeholder="Create a secure password" required>
                            <button class="password-toggle" type="button" data-password-toggle aria-label="Show password" aria-pressed="false"><i class="ph ph-eye" aria-hidden="true"></i></button>
                        </div>
                        @error('password')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-field">
                        <label for="password_confirmation">Confirm password</label>
                        <div class="password-field">
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Re-enter your password" required>
                            <button class="password-toggle" type="button" data-password-toggle aria-label="Show password" aria-pressed="false"><i class="ph ph-eye" aria-hidden="true"></i></button>
                        </div>
                        @error('password_confirmation')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <button class="btn-primary login-submit" type="submit"><i class="ph ph-user-plus" aria-hidden="true"></i>Create account</button>
                </form>

                <div class="login-support" aria-label="Registration help">
                    <i class="ph ph-question" aria-hidden="true"></i>
                    <p><strong>Already registered?</strong><span><a href="{{ route('login') }}" style="color: inherit; text-decoration: underline;">Sign in to your HIMS account</a></span></p>
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
