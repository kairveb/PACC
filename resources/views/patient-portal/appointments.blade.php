@extends('layouts.hims')

@section('title', 'My Appointments')
@section('page-title', 'My Appointments')
@section('page-kicker', 'Patient portal')
@section('page-description', 'Track scheduled visits and appointment history securely.')

@section('content')
<div class="card rounded-2xl border bg-white p-6 shadow-sm">
    @if ($appointments->isEmpty())
        <p class="text-sm text-slate-500">No appointments found.</p>
    @else
        <div class="space-y-3">
            @foreach ($appointments as $appointment)
                <div class="flex items-center justify-between rounded-xl border border-slate-200 p-4">
                    <div>
                        <p class="font-medium text-slate-900">{{ $appointment->appointmentType?->name ?? 'Consultation' }}</p>
                        <p class="text-sm text-slate-600">{{ $appointment->provider?->user?->name ?? 'Provider' }}</p>
                    </div>
                    <div class="text-right text-sm text-slate-600">
                        <p>{{ $appointment->starts_at?->format('M d, Y') }}</p>
                        <p>{{ $appointment->starts_at?->format('g:i A') }}</p>
                        <p class="mt-1 font-medium text-slate-900">{{ $appointment->status }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $appointments->links() }}
        </div>
    @endif
</div>
@endsection
