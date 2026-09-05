@extends('layouts.hims')

@section('title', 'Outpatient Encounters')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Outpatient Encounters</h1>
            <p class="text-sm text-slate-500 mt-1">Telehealth and Outpatient Care System (TOCS)</p>
        </div>
        <button type="button" class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700" data-bs-toggle="modal" data-bs-target="#encounterModal">New Encounter</button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                        <th class="py-3 px-4">Number</th><th class="py-3 px-4">Patient</th><th class="py-3 px-4">Provider</th><th class="py-3 px-4">Date/Time</th><th class="py-3 px-4">Type</th><th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($encounters as $enc)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="py-3 px-4 font-mono text-xs">{{ $enc->encounter_number }}</td>
                            <td class="py-3 px-4 font-medium"><a href="{{ route('patients.show', $enc->patient) }}" class="text-teal-600 hover:underline">{{ $enc->patient->full_name ?? '—' }}</a></td>
                            <td class="py-3 px-4">{{ $enc->provider->full_name ?? '—' }}</td>
                            <td class="py-3 px-4">{{ $enc->started_at?->format('M d, Y g:i A') ?? '—' }}</td>
                            <td class="py-3 px-4">{{ $enc->type }}</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 text-xs rounded-full bg-slate-100">{{ $enc->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-400">No encounters found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">{{ $encounters->links() }}</div>
    </div>
</div>

<div class="modal fade" id="encounterModal" tabindex="-1" aria-labelledby="encounterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-2xl">
            <div class="modal-header border-b border-slate-200 px-5 py-4">
                <div>
                    <h5 class="modal-title text-lg font-semibold text-slate-900" id="encounterModalLabel">New Clinical Encounter</h5>
                    <p class="mt-1 text-sm text-slate-500">Telehealth and Outpatient Care System (TOCS)</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-5 py-5">
                <form method="POST" action="{{ route('encounters.store') }}" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Patient *</label>
                            <select name="patient_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                <option value="">Select patient</option>
                                @foreach ($patientOptions as $patient)
                                    <option value="{{ $patient->id }}">{{ $patient->full_name }} — {{ $patient->mrn }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Provider *</label>
                            <select name="provider_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                <option value="">Select provider</option>
                                @foreach (App\Models\Provider::where('active', true)->get() as $provider)
                                    <option value="{{ $provider->id }}">{{ $provider->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Appointment (optional)</label>
                            <select name="appointment_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                <option value="">None</option>
                                @foreach (App\Models\Appointment::with('patient')->where('status', App\Models\Appointment::STATUS_CHECKED_IN)->get() as $apt)
                                    <option value="{{ $apt->id }}">{{ $apt->appointment_number }} — {{ $apt->patient->full_name ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Encounter Type *</label>
                            <select name="type" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                <option value="OUTPATIENT">Outpatient</option>
                                <option value="TELEHEALTH">Telehealth</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Chief Complaint</label>
                        <textarea name="chief_complaint" rows="2" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"></textarea>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <h4 class="mb-3 text-sm font-semibold text-slate-800">Vital Signs</h4>
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                            <div>
                                <label class="mb-1 block text-xs text-slate-500">BP</label>
                                <input name="bp" placeholder="120/80" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-slate-500">Heart Rate</label>
                                <input type="number" name="heart_rate" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-slate-500">Resp. Rate</label>
                                <input type="number" name="respiratory_rate" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-slate-500">Temp (°C)</label>
                                <input type="number" step="0.1" name="temperature" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-slate-500">SpO2</label>
                                <input type="number" name="spo2" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Clinical Notes</label>
                        <textarea name="notes" rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"></textarea>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Assessment</label>
                            <textarea name="assessment" rows="2" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"></textarea>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Plan</label>
                            <textarea name="plan" rows="2" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"></textarea>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Follow-up Date</label>
                        <input type="date" name="follow_up_date" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    </div>

                    <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                        <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700">Save Encounter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
