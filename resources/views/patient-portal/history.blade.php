@extends('layouts.hims')

@section('title', 'Medical History')
@section('page-title', 'Medical History')
@section('page-kicker', 'Patient portal')
@section('page-description', 'View recent clinical visits and documented encounters.')

@section('content')
<div class="card rounded-2xl border bg-white p-6 shadow-sm">
    @if ($history->isEmpty())
        <p class="text-sm text-slate-500">No medical history found.</p>
    @else
        <div class="space-y-4">
            @foreach ($history as $encounter)
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-medium text-slate-900">{{ $encounter->provider?->user?->name ?? 'Provider' }}</p>
                        <span class="text-xs uppercase tracking-wide text-slate-500">{{ $encounter->type }}</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">{{ $encounter->started_at?->format('M d, Y') }}</p>
                    <p class="mt-3 text-sm text-slate-700"><strong>Chief complaint:</strong> {{ $encounter->chief_complaint ?? 'Not recorded' }}</p>
                    <p class="mt-2 text-sm text-slate-700"><strong>Assessment:</strong> {{ $encounter->assessment ?? 'Not recorded' }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $history->links() }}
        </div>
    @endif
</div>
@endsection
