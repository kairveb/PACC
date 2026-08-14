<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HIMS') — {{ config('app.name', 'HIMS') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components/typography-accessibility.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pages/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pages/dashboard-command-center.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased" data-module="@yield('module', 'main')" data-page="@yield('page', 'dashboard')">
    <div class="app-shell" data-auth-guard>
        <aside class="sidebar" id="app-sidebar" aria-label="Primary navigation">
            <div class="sidebar-panel">
                <header class="sidebar-header">
                    <a class="sidebar-logo" href="{{ route('dashboard') }}" aria-label="HIMS home">
                        <span class="logo-icon" aria-hidden="true"><i class="ph-fill ph-cross"></i></span>
                        <span class="logo-text">
                            <strong>HIMS</strong>
                            <span class="brand-tagline">Care Coordination</span>
                            <span class="brand-suite">Main System</span>
                        </span>
                    </a>
                </header>

                <nav class="sidebar-nav" aria-label="HIMS systems">
                    <p class="nav-title">Overview</p>
                    <ul class="nav-list">
                        <li><a class="nav-link{{ request()->routeIs('dashboard') ? ' active' : '' }}" href="{{ route('dashboard') }}" aria-label="Dashboard" @if(request()->routeIs('dashboard')) aria-current="page" @endif><i class="ph-fill ph-squares-four" aria-hidden="true"></i><span class="nav-label">Dashboard</span></a></li>
                        @if (auth()->user()->hasRole('patient'))
                            <li><a class="nav-link{{ request()->routeIs('patients.portal', 'patients.portal.appointments', 'patients.portal.history', 'patients.portal.telehealth') ? ' active' : '' }}" href="{{ route('patients.portal') }}" aria-label="Patient Portal" @if(request()->routeIs('patients.portal', 'patients.portal.appointments', 'patients.portal.history', 'patients.portal.telehealth')) aria-current="page" @endif><i class="ph-fill ph-user-circle" aria-hidden="true"></i><span class="nav-label">Patient Portal</span></a></li>
                        @endif
                    </ul>

                    <p class="nav-title">Hospital Systems</p>
                    <ul class="nav-list nav-domain-list">
                        @can('view-patients')
                        <li class="nav-accordion{{ request()->routeIs('patients.*') ? ' is-expanded is-active' : '' }}">
                            <button class="nav-link nav-link-button nav-accordion__toggle" type="button" aria-expanded="{{ request()->routeIs('patients.*') ? 'true' : 'false' }}" aria-controls="nav-patients" aria-label="Patient Management"><i class="ph-fill ph-users-three" aria-hidden="true"></i><span class="nav-label">Patients</span><i class="ph ph-caret-down nav-chevron" aria-hidden="true"></i></button>
                            <ul class="nav-submenu" id="nav-patients" @if(!request()->routeIs('patients.*')) hidden @endif>
                                @can('create-patients')
                                    <li><a href="{{ route('patients.create') }}" class="{{ request()->routeIs('patients.create') ? 'active' : '' }}">Register Patient</a></li>
                                @endcan
                                @if (auth()->user()->hasAnyRole(['registration','doctor','nurse','super-admin','hospital-admin']))
                                    <li><a href="{{ route('patients.index') }}" class="{{ request()->routeIs('patients.index', 'patients.show', 'patients.vitals') ? 'active' : '' }}">Patient List</a></li>
                                @endif
                            </ul>
                        </li>
                        @endcan

                        @canAny(['view-appointments', 'view-encounters', 'view-telehealth', 'view-er'])
                        <li class="nav-accordion{{ request()->routeIs('appointments.*', 'outpatient.*', 'encounters.*', 'telehealth.*', 'emergency.*', 'doctors.queue*') ? ' is-expanded is-active' : '' }}">
                            <button class="nav-link nav-link-button nav-accordion__toggle" type="button" aria-expanded="{{ request()->routeIs('appointments.*', 'outpatient.*', 'encounters.*', 'telehealth.*', 'emergency.*', 'doctors.queue*') ? 'true' : 'false' }}" aria-controls="nav-care" aria-label="Care Delivery"><i class="ph-fill ph-heartbeat" aria-hidden="true"></i><span class="nav-label">Care Delivery</span><i class="ph ph-caret-down nav-chevron" aria-hidden="true"></i></button>
                            <ul class="nav-submenu" id="nav-care" @if(!request()->routeIs('appointments.*', 'outpatient.*', 'encounters.*', 'telehealth.*', 'emergency.*', 'doctors.queue*')) hidden @endif>
                                @can('view-appointments')
                                    <li><a href="{{ route('appointments.index') }}" class="{{ request()->routeIs('appointments.*') ? 'active' : '' }}">Appointments</a></li>
                                @endcan
                                @can('view-encounters')
                                    @if (auth()->user()->hasAnyRole(['doctor','super-admin','hospital-admin','nurse']))
                                        <li><a href="{{ route('outpatient.index') }}" class="{{ request()->routeIs('outpatient.index', 'encounters.*') ? 'active' : '' }}">Outpatient</a></li>
                                    @endif
                                    @if (auth()->user()->hasAnyRole(['doctor','super-admin','hospital-admin']))
                                        <li><a href="{{ route('doctors.queue') }}" class="{{ request()->routeIs('doctors.queue') ? 'active' : '' }}">Doctor Queue</a></li>
                                    @endif
                                @endcan
                                @can('view-telehealth')
                                    @if (auth()->user()->hasAnyRole(['doctor','nurse','super-admin','hospital-admin','patient']))
                                        <li><a href="{{ route('telehealth.index') }}" class="{{ request()->routeIs('telehealth.*') ? 'active' : '' }}">Telehealth</a></li>
                                    @endif
                                @endcan
                                @can('view-er')
                                    @if (auth()->user()->hasAnyRole(['nurse','doctor','super-admin','hospital-admin','registration']))
                                        <li><a href="{{ route('emergency.index') }}" class="{{ request()->routeIs('emergency.*') ? 'active' : '' }}">ER / Emergency</a></li>
                                    @endif
                                @endcan
                            </ul>
                        </li>
                        @endcanAny

                        @canAny(['view-beds', 'view-admissions'])
                        <li class="nav-accordion{{ request()->routeIs('beds.*', 'admissions.*', 'inpatient.*') ? ' is-expanded is-active' : '' }}">
                            <button class="nav-link nav-link-button nav-accordion__toggle" type="button" aria-expanded="{{ request()->routeIs('beds.*', 'admissions.*', 'inpatient.*') ? 'true' : 'false' }}" aria-controls="nav-inpatient" aria-label="Inpatient Services"><i class="ph-fill ph-bed" aria-hidden="true"></i><span class="nav-label">Inpatient</span><i class="ph ph-caret-down nav-chevron" aria-hidden="true"></i></button>
                            <ul class="nav-submenu" id="nav-inpatient" @if(!request()->routeIs('beds.*', 'admissions.*', 'inpatient.*')) hidden @endif>
                                @can('view-beds')
                                    @if (auth()->user()->hasAnyRole(['nurse','super-admin','hospital-admin']))
                                        <li><a href="{{ route('beds.index') }}" class="{{ request()->routeIs('beds.index', 'inpatient.index') ? 'active' : '' }}">Bed Board</a></li>
                                    @endif
                                @endcan
                                @can('view-admissions')
                                    @if (auth()->user()->hasAnyRole(['nurse','super-admin','hospital-admin']))
                                        <li><a href="{{ route('admissions.index') }}" class="{{ request()->routeIs('admissions.*') ? 'active' : '' }}">Admissions</a></li>
                                    @endif
                                @endcan
                            </ul>
                        </li>
                        @endcanAny

                        @canAny(['view-reports', 'view-audit-logs'])
                        <li class="nav-accordion{{ request()->routeIs('reports.*', 'audit.*') ? ' is-expanded is-active' : '' }}">
                            <button class="nav-link nav-link-button nav-accordion__toggle" type="button" aria-expanded="{{ request()->routeIs('reports.*', 'audit.*') ? 'true' : 'false' }}" aria-controls="nav-ops" aria-label="Operations"><i class="ph-fill ph-chart-pie-slice" aria-hidden="true"></i><span class="nav-label">Operations</span><i class="ph ph-caret-down nav-chevron" aria-hidden="true"></i></button>
                            <ul class="nav-submenu" id="nav-ops" @if(!request()->routeIs('reports.*', 'audit.*')) hidden @endif>
                                @can('view-reports')
                                    @if (auth()->user()->hasAnyRole(['super-admin','hospital-admin','doctor','nurse','registration']))
                                        <li><a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">Reports</a></li>
                                    @endif
                                @endcan
                                @can('view-audit-logs')
                                    @if (auth()->user()->hasAnyRole(['super-admin','hospital-admin']))
                                        <li><a href="{{ route('audit.index') }}" class="{{ request()->routeIs('audit.*') ? 'active' : '' }}">Audit Logs</a></li>
                                    @endif
                                @endcan
                            </ul>
                        </li>
                        @endcanAny
                    </ul>
                </nav>

                <footer class="sidebar-footer">
                    <div class="sidebar-profile-wrap">
                        <button class="sidebar-profile" id="profile-toggle" type="button" aria-label="Open account menu" aria-haspopup="menu" aria-expanded="false" aria-controls="profile-menu">
                            <span class="profile-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="profile-info"><span class="profile-name">{{ auth()->user()->name }}</span><span class="profile-role">{{ auth()->user()->roles->pluck('name')->join(', ') }}</span></span>
                            <i class="ph ph-caret-up-down profile-chevron" aria-hidden="true"></i>
                        </button>
                        <div class="profile-menu" id="profile-menu" role="menu" hidden>
                            <a class="profile-menu-link" href="{{ route('profile.edit') }}" role="menuitem"><i class="ph ph-user" aria-hidden="true"></i>Profile</a>
                            <div class="profile-menu-divider" role="separator"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="profile-menu-link profile-menu-logout" type="submit" role="menuitem"><i class="ph ph-sign-out" aria-hidden="true"></i>Logout</button>
                            </form>
                        </div>
                    </div>
                </footer>
            </div>
        </aside>
        <div class="sidebar-backdrop" data-sidebar-backdrop aria-hidden="true"></div>

        <main class="main-content" id="main-content">
            <header class="navbar-custom">
                <div class="navbar-left">
                    <button class="menu-toggle" type="button" aria-label="Collapse sidebar" aria-controls="app-sidebar" aria-expanded="true"><i class="ph ph-list" aria-hidden="true"></i></button>
                    <div class="app-identity"><p class="app-identity-title">HIMS Main System</p><span class="app-identity-subtitle">Patient Access &amp; Care Coordination</span></div>
                </div>
                <div class="navbar-center">
                    @can('view-patients')
                    <div class="search-wrap">
                        <label class="search-box" for="global-search"><i class="ph ph-magnifying-glass" aria-hidden="true"></i><span class="visually-hidden">Search HIMS</span><input id="global-search" type="search" placeholder="Search patients, appointments, or modules..." autocomplete="off" aria-controls="search-results" aria-expanded="false">
                        <kbd aria-hidden="true">/</kbd></label>
                        <div class="search-results" id="search-results" role="listbox" hidden></div>
                    </div>
                    @endcan
                </div>
                <div class="navbar-right">
                    <button class="icon-btn" type="button" aria-label="Notifications" disabled><i class="ph ph-bell" aria-hidden="true"></i></button>
                    <button class="icon-btn" type="button" aria-label="Messages" disabled><i class="ph ph-envelope-simple" aria-hidden="true"></i></button>
                </div>
            </header>

            <section class="page-wrapper" aria-label="@yield('page-label', 'HIMS workspace')">
                <div class="dashboard-page">
                    @hasSection('page-title')
                        <header class="dashboard-header motion-enter">
                            <div class="dashboard-header-main">
                                @hasSection('page-kicker')
                                    <p class="dashboard-kicker">@yield('page-kicker', 'Operations')</p>
                                @endif
                                <h1>@yield('page-title', 'Operations')</h1>
                                <p class="dashboard-lead">@yield('page-description', 'A modern operating view for patient access and care coordination.')</p>
                            </div>
                            <div class="dashboard-header-actions">
                                <div class="dashboard-session">
                                    <strong>{{ auth()->user()->name }}</strong>
                                    <span>{{ auth()->user()->roles->pluck('name')->join(', ') }}</span>
                                </div>
                                <div class="metric-pill">@yield('page-badge', 'Live workspace')</div>
                            </div>
                        </header>
                    @endif

                    @if (session('success'))
                        <div class="hims-notification hims-notification--success hims-notification--banner">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="hims-notification hims-notification--danger hims-notification--banner">{{ session('error') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="hims-notification hims-notification--danger hims-notification--banner">
                            <strong>Please fix the following errors:</strong>
                            <div>
                                <ul class="mt-2 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                    @yield('content')
                </div>
            </section>
        </main>
    </div>

    <div id="modal-portal" aria-live="polite"></div>
    <div id="toast-container" role="status" aria-live="polite" aria-atomic="true"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/core/theme-boot.js') }}"></script>
    <script src="{{ asset('assets/js/data/module-registry.js') }}"></script>
    <script src="{{ asset('assets/js/core/app-shell.js') }}"></script>
    @stack('scripts')
</body>
</html>
