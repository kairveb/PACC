@extends('layouts.hims')

@section('title', 'Reports')
@section('page-kicker', 'Insights')
@section('page-title', 'Operational Reports')
@section('page-badge', 'Analytics')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            ['patients', 'Patient Registrations', 'Daily patient registrations in a date range'],
            ['appointments', 'Appointment Volume', 'Appointment counts, cancellation, and no-show'],
            ['encounters', 'Outpatient Encounters', 'Encounter volume by type'],
            ['er', 'ER Volume & Triage', 'ER visits and triage priority distribution'],
            ['beds', 'Bed Occupancy', 'Current bed status and occupied admissions'],
            ['telehealth', 'Telehealth Usage', 'Telehealth sessions by status'],
        ] as [$route, $title, $desc])
            <a href="{{ route('reports.' . $route) }}" class="panel-card p-5 transition hover:-translate-y-1 hover:border-teal-400">
                <div class="font-semibold text-slate-800">{{ $title }}</div>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $desc }}</p>
                <span class="mt-4 inline-block text-sm font-semibold text-teal-600">View →</span>
            </a>
        @endforeach
    </div>
</div>
@endsection
