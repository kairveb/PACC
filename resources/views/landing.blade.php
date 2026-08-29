@extends('layouts.auth')

@section('title', 'HIMS | Connected Care')
@section('module', 'auth')
@section('page', 'landing')

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

        <section class="login-panel" aria-labelledby="landing-title">
            <div class="login-card">
                <header class="login-card-header">
                    <p class="page-kicker">Connected care</p>
                    <h2 id="landing-title">Care that is timely, compassionate, and close to home</h2>
                    <p class="login-help">Trusted access to patient services, clinical care, and community-focused support.</p>
                </header>

                <div class="login-support" aria-label="System overview">
                    <i class="ph ph-heart-pulse" aria-hidden="true"></i>
                    <p><strong>Patient coordination</strong><span>From intake to follow-up, we work to deliver prompt, respectful care to every patient and family.</span></p>
                </div>

                <aside class="login-access-notice" aria-label="Operations overview">
                    <i class="ph ph-clipboard-text" aria-hidden="true"></i>
                    <div><strong>Accessible services</strong><span>Designed to support safe, efficient care through transparent processes and compassionate service.</span></div>
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
