@extends('layouts.hims')

@section('title', 'Admissions')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Admissions</h1>
            <p class="text-sm text-slate-500 mt-1">Inpatient and Bed Management System (IBMS)</p>
        </div>
        <a href="{{ route('admissions.create') }}" class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700">New Admission</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <form method="GET" class="flex gap-3">
            <select name="status" class="px-3 py-2 text-sm border border-slate-300 rounded-lg">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-slate-800 text-white rounded-lg">Filter</button>
            <a href="{{ route('admissions.index') }}" class="px-4 py-2 text-sm border border-slate-300 rounded-lg">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                        <th class="py-3 px-4">Number</th><th class="py-3 px-4">Patient</th><th class="py-3 px-4">Status</th><th class="py-3 px-4">Bed</th><th class="py-3 px-4">Created</th><th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($admissions as $adm)
                        @php
                            $activeAssignment = $adm->bedAssignments->where('status', 'ACTIVE')->first();
                            $bed = $activeAssignment?->bed;
                        @endphp
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="py-3 px-4 font-mono text-xs">{{ $adm->admission_number }}</td>
                            <td class="py-3 px-4 font-medium">{{ $adm->patient->full_name ?? '—' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $adm->status === 'DISCHARGED' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $adm->status === 'ADMITTED' || $adm->status === 'TRANSFERRED' ? 'bg-teal-100 text-teal-700' : '' }}
                                    {{ $adm->status === 'REQUESTED' ? 'bg-amber-100 text-amber-700' : '' }}
                                ">{{ $adm->status }}</span>
                            </td>
                            <td class="py-3 px-4">{{ $bed?->label ?? '—' }}</td>
                            <td class="py-3 px-4 text-slate-500 text-xs">{{ $adm->created_at->format('M d, Y') }}</td>
                            <td class="py-3 px-4"><a href="{{ route('admissions.show', $adm) }}" class="text-teal-600 text-xs font-medium">Manage</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-400">No admissions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">{{ $admissions->links() }}</div>
    </div>
</div>
@endsection
