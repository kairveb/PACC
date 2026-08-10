<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'HIMS') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 font-[Poppins] text-slate-100 antialiased">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(45,212,191,0.25),_transparent_35%),linear-gradient(135deg,_#020617_0%,_#111827_55%,_#0f172a_100%)]">
            <div class="mx-auto flex min-h-screen max-w-7xl flex-col justify-center px-4 py-10 sm:px-6 lg:flex-row lg:px-8">
                <section class="mb-8 flex w-full flex-col justify-between rounded-[32px] border border-white/10 bg-slate-900/70 p-8 shadow-2xl shadow-black/30 backdrop-blur lg:mb-0 lg:mr-8 lg:w-[45%] lg:p-10">
                    <div>
                        <div class="mb-6 inline-flex items-center gap-3 rounded-full border border-teal-400/20 bg-teal-500/10 px-3 py-1 text-sm font-medium text-teal-200">
                            <span class="h-2.5 w-2.5 rounded-full bg-teal-400"></span>
                            HIMS Command Center
                        </div>
                        <p class="mb-3 text-sm uppercase tracking-[0.35em] text-slate-400">Hospital Information Management System</p>
                        <h1 class="text-3xl font-semibold text-white sm:text-4xl">Patient access and care coordination, unified.</h1>
                        <p class="mt-4 max-w-xl text-base leading-7 text-slate-300">
                            Sign in to access the operations workspace for registrations, appointments, telehealth, ER flow, admissions, and reporting.
                        </p>
                    </div>

                    <div class="mt-10 space-y-4 rounded-2xl border border-slate-800 bg-slate-950/60 p-5 text-sm text-slate-300">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 text-teal-400">●</span>
                            <span>Secure authentication for clinical and administrative staff.</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 text-teal-400">●</span>
                            <span>Role-based navigation for admissions, nursing, ER, and care teams.</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 text-teal-400">●</span>
                            <span>Live coordination tools for scheduling, bed status, and operational visibility.</span>
                        </div>
                    </div>
                </section>

                <section class="w-full lg:w-[55%]">
                    <div class="rounded-[32px] border border-white/10 bg-white/95 p-6 shadow-2xl shadow-slate-950/20 backdrop-blur sm:p-8 lg:p-10">
                        @hasSection('content')
                            @yield('content')
                        @else
                            {{ $slot }}
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </body>
</html>
