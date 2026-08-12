@extends('layouts.hims')

@section('title', 'Telehealth')
@section('page-kicker', 'TOCS')
@section('page-title', 'Telehealth & Outpatient Care')
@section('page-badge', 'Live workspace')

@section('content')
<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-4">
        <div class="panel-card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-teal-600">Live</div>
                <span class="status-pill success">Active</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">—</div>
            <p class="mt-2 text-sm text-slate-500">Active telehealth sessions</p>
        </div>
        <div class="panel-card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-700">Queue</div>
                <span class="status-pill info">Waiting</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">—</div>
            <p class="mt-2 text-sm text-slate-500">Patients in queue</p>
        </div>
        <div class="panel-card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-emerald-600">Done</div>
                <span class="status-pill success">Complete</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">—</div>
            <p class="mt-2 text-sm text-slate-500">Completed consults</p>
        </div>
        <div class="panel-card p-5">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-indigo-600">Rx</div>
                <span class="status-pill warning">Pending</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">—</div>
            <p class="mt-2 text-sm text-slate-500">E-prescriptions generated</p>
        </div>
    </div>

    <div class="panel-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Session controls</h2>
                <p class="text-sm text-slate-600">Select a registered patient to attach to the telehealth session.</p>
            </div>
        </div>
        <div class="space-y-4 p-5">
            <div class="grid gap-4 md:grid-cols-2 md:items-start">
                <div class="space-y-3">
                    <label for="telehealthPatientName" class="mb-1 block text-sm font-medium text-slate-700">Patient</label>
                    <input list="telehealthPatientList" id="telehealthPatientName" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Search registered patient or enter name">
                    <datalist id="telehealthPatientList"></datalist>
                    <p class="text-sm text-slate-500">Leave empty to start an unassigned telehealth room.</p>
                </div>
                <div class="flex flex-wrap items-start justify-end gap-3">
                    <button type="button" id="launchTelehealthBtn" class="rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Launch video call</button>
                    <button type="button" id="generatePrescriptionBtn" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Generate prescription</button>
                    <button type="button" id="sendReminderBtn" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Send reminder</button>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                        <th class="py-3 px-4">Patient</th><th class="py-3 px-4">Provider</th><th class="py-3 px-4">Start</th><th class="py-3 px-4">Duration</th><th class="py-3 px-4">Meeting</th><th class="py-3 px-4">Status</th><th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr class="border-b border-slate-100 hover:bg-slate-50" data-session-id="{{ $session->id }}" data-appointment-id="{{ $session->appointment_id }}">
                            <td class="py-3 px-4 font-medium">{{ $session->appointment->patient->full_name ?? '—' }}</td>
                            <td class="py-3 px-4">{{ $session->appointment->provider->full_name ?? '—' }}</td>
                            <td class="py-3 px-4">{{ $session->start_time?->format('M d, Y g:i A') }}</td>
                            <td class="py-3 px-4">{{ $session->duration }} min</td>
                            <td class="py-3 px-4 font-mono text-xs">{{ $session->zoom_meeting_id ?? 'Not configured' }}</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 text-xs rounded-full bg-slate-100">{{ $session->status }}</span></td>
                            <td class="py-3 px-4 flex gap-2">
                                <a href="{{ route('telehealth.show', $session) }}" class="text-teal-600 text-xs font-medium">View</a>
                                <button type="button" class="telehealth-open-session text-xs font-medium text-blue-600" data-session-id="{{ $session->id }}">Open</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">No telehealth sessions.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">{{ $sessions->links() }}</div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;

        async function apiRequest(url, method = 'GET', payload = null) {
            const headers = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
            if (token) headers['X-CSRF-TOKEN'] = token;
            if (payload !== null) headers['Content-Type'] = 'application/json';

            const response = await fetch(url, {
                method,
                credentials: 'same-origin',
                headers,
                body: payload ? JSON.stringify(payload) : undefined,
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || 'Request failed.');
            }

            return data;
        }

        const launchBtn = document.getElementById('launchTelehealthBtn');
        const generatePrescriptionBtn = document.getElementById('generatePrescriptionBtn');
        const sendReminderBtn = document.getElementById('sendReminderBtn');
        const primaryRow = document.querySelector('[data-session-id]');

        async function startSession(sessionId = null, appointmentId = null) {
            try {
                if (sessionId) {
                    const response = await apiRequest(`/api/v1/telehealth/${sessionId}/start`, 'POST', { started_at: new Date().toISOString() });
                    if (response.data?.id) {
                        window.location.href = `/telehealth/${response.data.id}`;
                    }
                    return;
                }

                if (!appointmentId) {
                    if (window.hisToast) hisToast('No active appointment is available to launch a telehealth session.', 'info');
                    return;
                }

                const response = await apiRequest('/api/v1/telehealth', 'POST', { appointment_id: Number(appointmentId) });
                if (response.data?.id) {
                    await apiRequest(`/api/v1/telehealth/${response.data.id}/start`, 'POST', { started_at: new Date().toISOString() });
                    window.location.href = `/telehealth/${response.data.id}`;
                }
            } catch (error) {
                if (window.hisToast) hisToast(error.message, 'danger');
            }
        }

        async function createPrescription(sessionId = null) {
            if (!sessionId) {
                if (window.hisToast) hisToast('Select a telehealth session to generate a prescription.', 'info');
                return;
            }

            try {
                const response = await apiRequest(`/api/v1/telehealth/${sessionId}/prescription`, 'POST', {
                    medications: ['Amoxicillin 500mg', 'Ibuprofen 200mg'],
                    notes: 'Continue as directed and review if symptoms worsen.',
                });

                if (response.success) {
                    if (window.hisToast) hisToast('Prescription generated and saved to the patient record.', 'success');
                }
            } catch (error) {
                if (window.hisToast) hisToast(error.message, 'danger');
            }
        }

        async function sendReminder(sessionId = null) {
            if (!sessionId) {
                if (window.hisToast) hisToast('Select a telehealth session to send a reminder.', 'info');
                return;
            }

            try {
                const response = await apiRequest(`/api/v1/telehealth/${sessionId}/reminder`, 'POST', {
                    channel: 'email',
                    message: 'This is your telehealth appointment reminder. Please join on time.',
                });

                if (response.success) {
                    if (window.hisToast) hisToast('Reminder sent to the patient.', 'success');
                }
            } catch (error) {
                if (window.hisToast) hisToast(error.message, 'danger');
            }
        }

        if (launchBtn) {
            launchBtn.addEventListener('click', function () {
                const row = primaryRow || document.querySelector('[data-session-id]');
                const sessionId = row?.dataset.sessionId || null;
                const appointmentId = row?.dataset.appointmentId || null;
                startSession(sessionId, appointmentId);
            });
        }

        if (generatePrescriptionBtn) {
            generatePrescriptionBtn.addEventListener('click', function () {
                const sessionId = primaryRow?.dataset.sessionId || null;
                createPrescription(sessionId);
            });
        }

        if (sendReminderBtn) {
            sendReminderBtn.addEventListener('click', function () {
                const sessionId = primaryRow?.dataset.sessionId || null;
                sendReminder(sessionId);
            });
        }

        document.querySelectorAll('.telehealth-open-session').forEach((button) => {
            button.addEventListener('click', function () {
                const sessionId = this.dataset.sessionId;
                if (sessionId) startSession(sessionId, null);
            });
        });

        document.querySelectorAll('[data-session-id]').forEach((row) => {
            row.addEventListener('dblclick', function () {
                const sessionId = this.dataset.sessionId;
                if (sessionId) startSession(sessionId, null);
            });
        });
    })();
</script>
@endpush
@endsection
