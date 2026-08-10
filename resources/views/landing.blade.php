<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'HIMS') }} | Connected Care</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(45,212,191,0.18),_transparent_35%),linear-gradient(135deg,_#020617_0%,_#111827_45%,_#0f172a_100%)]">
            <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-10 lg:px-10">
                <header class="flex flex-wrap items-center justify-between gap-4 rounded-full border border-white/10 bg-white/10 px-4 py-3 backdrop-blur">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300">HIMS</p>
                        <p class="text-lg font-semibold text-white">Hospital Information Management Suite</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="rounded-full bg-teal-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-teal-400">Open Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">Sign In</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-slate-100">Create Account</a>
                            @endif
                        @endauth
                    </div>
                </header>

                <main class="flex flex-1 flex-col justify-center py-16 lg:flex-row lg:items-center lg:gap-16">
                    <section class="max-w-2xl">
                        <span class="inline-flex items-center rounded-full border border-teal-400/30 bg-teal-500/10 px-3 py-1 text-sm font-medium text-teal-200">
                            Connected care for patients, staff, and operations
                        </span>
                        <h1 class="mt-6 text-4xl font-bold leading-tight text-white sm:text-5xl">
                            Keep every patient touchpoint visible in one calm workspace.
                        </h1>
                        <p class="mt-5 max-w-xl text-lg leading-8 text-slate-300">
                            Manage registrations, appointments, ER flow, admissions, telehealth sessions, and clinical summaries from a single elegant dashboard designed for modern hospitals.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="rounded-xl bg-teal-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-teal-400">Go to Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="rounded-xl bg-teal-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-teal-400">Get Started</a>
                            @endif
                            <a href="{{ route('patients.index') }}" class="rounded-xl border border-white/15 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">Review Patient Records</a>
                        </div>

                        <div class="mt-10 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                                <p class="text-2xl font-semibold text-white">24/7</p>
                                <p class="mt-1 text-sm text-slate-300">Operational visibility</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                                <p class="text-2xl font-semibold text-white">100%</p>
                                <p class="mt-1 text-sm text-slate-300">Digital workflows</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                                <p class="text-2xl font-semibold text-white">Live</p>
                                <p class="mt-1 text-sm text-slate-300">Telehealth &amp; admissions</p>
                            </div>
                        </div>
                    </section>

                    <section class="w-full max-w-xl rounded-3xl border border-white/10 bg-slate-900/70 p-6 shadow-2xl shadow-slate-950/50 backdrop-blur">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">Core modules</p>
                                <h2 class="mt-2 text-xl font-semibold text-white">Everything your care team needs</h2>
                            </div>
                            <div class="rounded-full bg-teal-500/15 px-3 py-1 text-sm font-medium text-teal-300">Ready</div>
                        </div>
                        <div class="mt-6 space-y-3">
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="font-semibold text-white">Patient 360°</p>
                                <p class="mt-1 text-sm text-slate-300">Complete patient records, alerts, appointments, encounters, and admissions.</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="font-semibold text-white">ER and Bed Flow</p>
                                <p class="mt-1 text-sm text-slate-300">Triage queues, bed assignments, and inpatient movement in a single view.</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="font-semibold text-white">Reports &amp; Audit</p>
                                <p class="mt-1 text-sm text-slate-300">Track performance, utilization, and activity at a glance.</p>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </body>
</html>
