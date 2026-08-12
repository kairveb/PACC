@extends('layouts.hims')

@section('title', 'Telehealth Session')

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Telehealth Session</h1>
        <p class="text-sm text-slate-500 mt-1">Appointment {{ $session->appointment->appointment_number ?? '—' }}</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <div><span class="text-slate-500">Patient:</span> <span class="font-medium">{{ $session->appointment?->patient?->full_name ?? '—' }}</span></div>
            <div><span class="text-slate-500">Provider:</span> <span class="font-medium">{{ $session->appointment?->provider?->full_name ?? '—' }}</span></div>
            <div><span class="text-slate-500">Start:</span> {{ $session->start_time?->format('M d, Y g:i A') }}</div>
            <div><span class="text-slate-500">Duration:</span> {{ $session->duration }} minutes</div>
            <div><span class="text-slate-500">Status:</span> <span class="px-2 py-1 text-xs rounded-full bg-slate-100">{{ $session->status }}</span></div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-800 mb-3">Meeting Information</h3>
        @if ($session->zoom_meeting_id && $session->join_url)
            <div class="space-y-2 text-sm">
                <p>Meeting ID: <span class="font-mono">{{ $session->zoom_meeting_id }}</span></p>
                <p>Join URL: <a href="{{ $session->join_url }}" target="_blank" class="text-teal-600 hover:underline">{{ $session->join_url }}</a></p>
            </div>
        @else
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
                Zoom is not configured. Telehealth record tracked locally. Enable <code>ZOOM_ENABLED=true</code> and API credentials in <code>.env</code> to create live meetings.
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-800 mb-3">Create / Update Meeting</h3>
        <form method="POST" action="{{ route('telehealth.create-meeting', $session) }}" class="flex gap-3 items-end">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Start Date/Time</label>
                <input type="datetime-local" name="start_time" value="{{ $session->start_time?->format('Y-m-d\TH:i') }}" class="px-3 py-2 text-sm border border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Duration (min)</label>
                <input type="number" name="duration" value="{{ $session->duration }}" class="px-3 py-2 text-sm border border-slate-300 rounded-lg w-28">
            </div>
            <button type="submit" class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg">Create/Update Meeting</button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="font-semibold text-slate-800">Closeout consultation</h3>
            <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700">Clinical summary</span>
        </div>

        <form id="closeoutForm" class="space-y-4">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="assessment" class="mb-1 block text-sm font-medium text-slate-700">Assessment</label>
                    <textarea id="assessment" name="assessment" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Document the patient assessment and findings."></textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="plan" class="mb-1 block text-sm font-medium text-slate-700">Plan</label>
                    <textarea id="plan" name="plan" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Outline treatment plan and recommended follow-up."></textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="discharge_instructions" class="mb-1 block text-sm font-medium text-slate-700">Discharge instructions</label>
                    <textarea id="discharge_instructions" name="discharge_instructions" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Any care instructions or self-management advice."></textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="clinic_note" class="mb-1 block text-sm font-medium text-slate-700">Clinic note</label>
                    <textarea id="clinic_note" name="clinic_note" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Add clinician documentation for the telehealth consultation."></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700" onclick="window.location.reload()">Reset</button>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Save closeout</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('closeoutForm');
        const token = document.querySelector('meta[name="csrf-token"]')?.content;

        if (!form) return;

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const payload = {
                assessment: document.getElementById('assessment')?.value || null,
                plan: document.getElementById('plan')?.value || null,
                discharge_instructions: document.getElementById('discharge_instructions')?.value || null,
                clinic_note: document.getElementById('clinic_note')?.value || null,
            };

            try {
                const response = await fetch('/api/v1/telehealth/{{ $session->id }}/closeout', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.message || 'Unable to save the consultation closeout.');
                }

                if (window.hisToast) {
                    hisToast('Telehealth consultation closeout saved.', 'success');
                }

                window.location.href = "{{ route('telehealth.index') }}";
            } catch (error) {
                if (window.hisToast) {
                    hisToast(error.message, 'danger');
                }
            }
        });
    })();
</script>
@endpush
@endsection
