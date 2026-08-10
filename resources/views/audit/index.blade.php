@extends('layouts.hims')

@section('title', 'Audit Logs')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Audit Logs</h1>
        <p class="text-sm text-slate-500 mt-1">System accountability and security</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <form method="GET" class="flex gap-3">
            <select name="action" class="px-3 py-2 text-sm border border-slate-300 rounded-lg">
                <option value="">All actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-slate-800 text-white rounded-lg">Filter</button>
            <a href="{{ route('audit.index') }}" class="px-4 py-2 text-sm border border-slate-300 rounded-lg">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                        <th class="py-3 px-4">User</th><th class="py-3 px-4">Action</th><th class="py-3 px-4">Resource</th><th class="py-3 px-4">Resource ID</th><th class="py-3 px-4">Result</th><th class="py-3 px-4">IP</th><th class="py-3 px-4">Time</th>
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
@endsection
