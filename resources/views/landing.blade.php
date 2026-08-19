@extends('layouts.auth')

@section('title', 'HIMS | Connected Care')
@section('module', 'auth')
@section('page', 'landing')

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

        <section class="login-panel" aria-labelledby="landing-title">
            <div class="login-card">
                <header class="login-card-header">
                    <p class="page-kicker">Connected care</p>
                    <h2 id="landing-title">HIMS access ready</h2>
                    <p class="login-help">Streamlined access for patient intake, clinical workflows, and hospital operations.</p>
                </header>

                <div class="login-support" aria-label="System overview">
                    <i class="ph ph-heart-pulse" aria-hidden="true"></i>
                    <p><strong>Patient coordination</strong><span>Admissions, telehealth, ER flow, and scheduling in one workspace.</span></p>
                </div>

                <aside class="login-access-notice" aria-label="Operations overview">
                    <i class="ph ph-clipboard-text" aria-hidden="true"></i>
                    <div><strong>Operations overview</strong><span>Use the hospital access profile to manage triage, reporting, and care coordination.</span></div>
                </aside>

                @auth
                    <a class="btn-primary login-submit" href="{{ route('dashboard') }}"><i class="ph ph-house" aria-hidden="true"></i>Open dashboard</a>
                @else
                    <a class="btn-primary login-submit" href="{{ route('login') }}"><i class="ph ph-sign-in" aria-hidden="true"></i>Sign in</a>
                    @if (Route::has('register'))
                        <a class="btn-outline login-submit" style="margin-top: 12px;" href="{{ route('register') }}">Create account</a>
                    @endif
                @endauth

                <footer class="login-card-footer"><span>Secure hospital access</span><span>HIMS</span></footer>
            </div>
        </section>
    </main>
@endsection
