@extends('layouts.hims')

@section('title', 'Admission Detail')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Admission {{ $admission->admission_number }}</h1>
            <p class="text-sm text-slate-500 mt-1">Patient: <span class="font-medium">{{ $admission->patient->full_name ?? '—' }}</span> · {{ $admission->patient->mrn ?? '' }}</p>
        </div>
        <div class="flex gap-2">
            @can('manage-admissions')
                @if ($admission->status === 'REQUESTED')
                    <form method="POST" action="{{ route('admissions.approve', $admission) }}">@csrf
                        <button class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">Approve</button>
                    </form>
                @endif
            @endcan
            @can('manage-admissions')
                @if (in_array($admission->status, ['ADMITTED', 'TRANSFERRED']))
                    <button onclick="document.getElementById('discharge-form').classList.toggle('hidden')" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">Discharge</button>
                @endif
            @endcan
        </div>
    </div>

    @can('manage-admissions')
        {{-- Discharge form --}}
        <div id="discharge-form" class="hidden bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-800 mb-3">Discharge Patient</h3>
            <form method="POST" action="{{ route('admissions.discharge', $admission) }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Reason</label><input type="text" name="reason" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Disposition</label><input type="text" name="disposition" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                </div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Notes</label><textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></textarea></div>
                <button type="submit" class="px-6 py-2.5 text-sm bg-blue-600 text-white rounded-lg" onclick="return confirm('Discharge this patient and release the bed?')">Confirm Discharge</button>
            </form>
        </div>
    @endcan

    @can('manage-beds')
        {{-- Bed assignment / reservation --}}
        @if (in_array($admission->status, ['REQUESTED', 'APPROVED']))
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-800 mb-3">Assign Bed</h3>
            <form method="POST" action="{{ route('admissions.admit', $admission) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Select Bed *</label>
                    <select name="bed_id" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                        <option value="">Choose bed</option>
                        @foreach ($availableBeds as $bed)
                            <option value="{{ $bed->id }}">{{ $bed->label }} ({{ $bed->room?->ward?->name ?? '—' }})</option>
                        @endforeach
                    </select>
                    @error('bed_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="px-6 py-2.5 text-sm bg-teal-600 text-white rounded-lg">Admit &amp; Assign Bed</button>
            </form>
        </div>
        @endif
    @endcan

    @can('manage-beds')
        {{-- Transfer form --}}
        @if (in_array($admission->status, ['ADMITTED', 'TRANSFERRED']))
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-800 mb-3">Transfer Patient</h3>
            <form method="POST" action="{{ route('admissions.transfer', $admission) }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Destination Bed *</label>
                        <select name="to_bed_id" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                            <option value="">Choose bed</option>
                            @foreach ($availableBeds as $bed)
                                <option value="{{ $bed->id }}">{{ $bed->label }} ({{ $bed->room?->ward?->name ?? '—' }})</option>
                            @endforeach
                        </select>
                        @error('to_bed_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Reason</label><input type="text" name="reason" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
                </div>
                <button type="submit" class="px-6 py-2.5 text-sm bg-amber-600 text-white rounded-lg">Transfer</button>
            </form>
        </div>
        @endif
    @endcan

    {{-- Admission details --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div><span class="text-slate-500">Status:</span> <span class="px-2 py-1 text-xs rounded-full bg-slate-100 ml-1">{{ $admission->status }}</span></div>
            <div><span class="text-slate-500">Attending:</span> <span class="font-medium">{{ $admission->attendingProvider->full_name ?? '—' }}</span></div>
            <div><span class="text-slate-500">Reason:</span> {{ $admission->reason ?? '—' }}</div>
        </div>
    </div>

    {{-- Bed assignment history --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200"><h3 class="font-semibold text-slate-800">Bed Assignment History</h3></div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-xs uppercase text-slate-500 bg-slate-50 border-b border-slate-200"><th class="py-3 px-4">Bed</th><th class="py-3 px-4">Assigned</th><th class="py-3 px-4">Released</th><th class="py-3 px-4">Status</th></tr></thead>
                <tbody>
                    @forelse ($admission->bedAssignments as $ba)
                        <tr class="border-b border-slate-100">
                            <td class="py-3 px-4 font-medium">{{ $ba->bed->label ?? '—' }}</td>
                            <td class="py-3 px-4">{{ $ba->assigned_at->format('M d, Y g:i A') }}</td>
                            <td class="py-3 px-4">{{ $ba->released_at?->format('M d, Y g:i A') ?? '—' }}</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 text-xs rounded-full bg-slate-100">{{ $ba->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-400">No bed assignment yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Transfer history --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200"><h3 class="font-semibold text-slate-800">Transfer History</h3></div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-xs uppercase text-slate-500 bg-slate-50 border-b border-slate-200"><th class="py-3 px-4">From</th><th class="py-3 px-4">To</th><th class="py-3 px-4">Time</th><th class="py-3 px-4">Reason</th></tr></thead>
                <tbody>
                    @forelse ($admission->transfers as $tr)
                        <tr class="border-b border-slate-100">
                            <td class="py-3 px-4">{{ $tr->fromBed->label ?? '—' }}</td>
                            <td class="py-3 px-4">{{ $tr->toBed->label ?? '—' }}</td>
                            <td class="py-3 px-4">{{ $tr->transferred_at->format('M d, Y g:i A') }}</td>
                            <td class="py-3 px-4">{{ $tr->reason ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-400">No transfers.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-200">
            @if ($admission->discharge)
                <p class="text-sm text-slate-600">Discharged: <span class="font-medium">{{ $admission->discharge->discharged_at->format('M d, Y g:i A') }}</span> — {{ $admission->discharge->reason ?? '—' }} ({{ $admission->discharge->disposition ?? '—' }})</p>
            @endif
        </div>
    </div>
</div>
@endsection
