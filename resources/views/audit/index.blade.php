@extends('layouts.hims')

@section('title', 'Audit Logs')

@section('content')
<div class="space-y-6">
    <div class="panel-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Audit Logs</h2>
                <p class="text-sm text-slate-600">System accountability and security</p>
            </div>
            <div class="flex items-center gap-2">
                @if (request()->hasAny(['action']))
                    <a href="{{ route('audit.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Clear filters</a>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">User</th>
                        <th class="relative py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <div class="flex items-center gap-2">
                                <span>Action</span>
                                <button type="button" data-filter-trigger data-filter-target="audit-action-filter" aria-expanded="false" class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-700" aria-label="Filter audit action">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                        <path fill-rule="evenodd" d="M2.75 4.5A.75.75 0 0 1 3.5 3.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            <div id="audit-action-filter" class="filter-panel hidden absolute left-0 top-full z-20 mt-2 w-64 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                                <form method="GET" class="space-y-3">
                                    <select name="action" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                        <option value="">All actions</option>
                                        @foreach ($actions as $action)
                                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                                        @endforeach
                                    </select>
                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </th>
                        <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Resource</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Resource ID</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Result</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">IP</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-b border-slate-100">
                            <td class="py-3 px-4">{{ $log->user->name ?? 'System' }}</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 text-xs rounded-full bg-slate-100 font-mono">{{ $log->action }}</span></td>
                            <td class="py-3 px-4">{{ $log->resource_type }}</td>
                            <td class="py-3 px-4 font-mono text-xs">{{ $log->resource_id ?? '—' }}</td>
                            <td class="py-3 px-4">{{ $log->result ?? '—' }}</td>
                            <td class="py-3 px-4 font-mono text-xs">{{ $log->ip_address ?? '—' }}</td>
                            <td class="py-3 px-4 text-slate-500 text-xs">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">No audit logs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">{{ $logs->links() }}</div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const triggers = document.querySelectorAll('[data-filter-trigger]');
        const panels = document.querySelectorAll('.filter-panel');

        const closePanels = () => {
            panels.forEach((panel) => panel.classList.add('hidden'));
            triggers.forEach((trigger) => trigger.setAttribute('aria-expanded', 'false'));
        };

        triggers.forEach((trigger) => {
            trigger.addEventListener('click', function (event) {
                event.stopPropagation();
                const targetId = trigger.getAttribute('data-filter-target');
                const panel = document.getElementById(targetId);
                const isOpen = !!panel && !panel.classList.contains('hidden');

                closePanels();

                if (!isOpen && panel) {
                    panel.classList.remove('hidden');
                    trigger.setAttribute('aria-expanded', 'true');
                }
            });
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('[data-filter-trigger]') && !event.target.closest('.filter-panel')) {
                closePanels();
            }
        });
    });
</script>
@endsection
