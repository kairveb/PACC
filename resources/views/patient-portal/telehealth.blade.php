@extends('layouts.hims')

@section('title', 'Telehealth')
@section('page-title', 'Telehealth Sessions')
@section('page-kicker', 'Patient portal')
@section('page-description', 'Secure access to your upcoming and recent virtual visits.')

@section('content')
<div class="card rounded-2xl border bg-white p-6 shadow-sm">
    @if ($sessions->isEmpty())
        <p class="text-sm text-slate-500">No telehealth sessions found.</p>
    @else
        <div class="space-y-3">
            @foreach ($sessions as $session)
                @php
                    $isLive = in_array($session->status, [\App\Models\TelehealthSession::STATUS_ACTIVE, \App\Models\TelehealthSession::STATUS_ONGOING], true);
                @endphp
                <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 p-4" data-start-at="{{ $session->start_time?->toIso8601String() }}" data-live="{{ $isLive ? '1' : '0' }}">
                    <div>
                        <p class="font-medium text-slate-900">{{ $session->appointment?->provider?->user?->name ?? 'Provider' }}</p>
                        <p class="text-sm text-slate-600">{{ $session->appointment?->appointmentType?->name ?? 'Telehealth' }}</p>
                        <p class="mt-1 text-xs countdown-inline {{ $isLive ? 'text-emerald-600' : 'text-slate-500' }}" data-countdown="{{ $session->start_time?->toIso8601String() }}">
                            {{ $isLive ? 'Live now' : $session->displayStatus() }}
                        </p>
                    </div>
                    <div class="text-right text-sm text-slate-600">
                        <p>{{ $session->start_time?->format('M d, Y') }}</p>
                        <p>{{ $session->start_time?->format('g:i A') }}</p>
                        <p class="mt-1 font-medium text-slate-900">{{ $session->displayStatus() }}</p>
                    </div>
                    @if ($session->join_url)
                        <a href="{{ $session->join_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center rounded-lg bg-teal-600 px-3 py-2 text-sm font-medium text-white hover:bg-teal-700 {{ $isLive ? '' : 'opacity-80' }}">
                            {{ $isLive ? 'Join room' : 'View room' }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $sessions->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    (function () {
        function updateCountdowns() {
            document.querySelectorAll('[data-countdown]').forEach((el) => {
                const start = new Date(el.dataset.countdown);
                const now = new Date();
                const diff = start.getTime() - now.getTime();
                const row = el.closest('[data-live]');
                const live = row && row.dataset.live === '1';

                if (live) {
                    el.textContent = 'Live now';
                    el.className = 'mt-1 text-xs countdown-inline text-emerald-600';
                    return;
                }

                if (diff <= 0) {
                    el.textContent = 'Starting';
                    el.className = 'mt-1 text-xs countdown-inline text-amber-600';
                    return;
                }

                const totalSeconds = Math.max(0, Math.floor(diff / 1000));
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;
                el.textContent = hours > 0 ? `${hours}h ${minutes}m ${seconds}s` : `${minutes}m ${seconds}s`;
                el.className = 'mt-1 text-xs countdown-inline text-slate-500';
            });
        }

        updateCountdowns();
        setInterval(updateCountdowns, 1000);
    })();
</script>
@endpush
@endsection
