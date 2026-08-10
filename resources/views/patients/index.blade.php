@extends('layouts.hims')

@section('title', 'Patients')
@section('page-kicker', 'Patient records')
@section('page-title', 'Patients')
@section('page-badge', 'Registry')

@section('content')
<div class="space-y-6">
    <div class="panel-card p-5">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by MRN, name, phone, email..." class="flex-1 min-w-[250px]">
            <input type="date" name="date_of_birth" value="{{ request('date_of_birth') }}">
            <select name="sex">
                <option value="">All sexes</option>
                <option value="Male" {{ request('sex') === 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ request('sex') === 'Female' ? 'selected' : '' }}>Female</option>
                <option value="Other" {{ request('sex') === 'Other' ? 'selected' : '' }}>Other</option>
            </select>
            <button type="submit" class="bg-slate-900">Search</button>
            <a href="{{ route('patients.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
        </form>
    </div>

    <div class="panel-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Patient directory</h2>
                <p class="text-sm text-slate-600">Search and manage patient records</p>
            </div>
            <a href="{{ route('patients.create') }}" class="rounded-2xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Register Patient</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="text-left">MRN</th>
                        <th class="text-left">Name</th>
                        <th class="text-left">Age/Sex</th>
                        <th class="text-left">Contact</th>
                        <th class="text-left">Status</th>
                        <th class="text-left">Registered</th>
                        <th class="text-left"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($patients as $patient)
                        <tr>
                            <td class="font-mono text-xs">{{ $patient->mrn }}</td>
                            <td class="font-medium text-slate-900">{{ $patient->full_name }}</td>
                            <td>{{ $patient->age }} / {{ $patient->sex }}</td>
                            <td class="text-slate-600">{{ $patient->phone ?? '—' }}</td>
                            <td>
                                <span class="status-pill {{ $patient->verified ? 'success' : 'warning' }}">{{ $patient->verified ? 'Verified' : 'Pending' }}</span>
                            </td>
                            <td class="text-slate-500">{{ $patient->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('patients.show', $patient) }}" class="text-sm font-semibold text-teal-600 hover:text-teal-700">View 360°</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">No patients found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">
            {{ $patients->links() }}
        </div>
    </div>
</div>
@endsection
