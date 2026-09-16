@extends('layouts.hims')

@section('title', 'Telehealth')
@section('page-kicker', 'TOCS')
@section('page-title', 'Telehealth & Outpatient Care')
@section('page-badge', 'Live workspace')

@section('content')
<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-4">
        <a href="{{ route('telehealth.index', ['status' => 'live']) }}" class="panel-card block p-5 transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-teal-600">Live</div>
                <span class="status-pill success">Ongoing</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ $sessions->whereIn('status', [\App\Models\TelehealthSession::STATUS_ACTIVE, \App\Models\TelehealthSession::STATUS_ONGOING])->count() }}</div>
            <p class="mt-2 text-sm text-slate-500">Active telehealth sessions</p>
        </a>

        <a href="{{ route('telehealth.index', ['status' => \App\Models\TelehealthSession::STATUS_SCHEDULED]) }}" class="panel-card block p-5 transition hover:-translate-y-0.5 hover:border-sky-200 hover:shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-700">Scheduled</div>
                <span class="status-pill info">Upcoming</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ $sessions->where('status', \App\Models\TelehealthSession::STATUS_SCHEDULED)->count() }}</div>
            <p class="mt-2 text-sm text-slate-500">Patients scheduled</p>
        </a>

        <a href="{{ route('telehealth.index', ['status' => \App\Models\TelehealthSession::STATUS_COMPLETED]) }}" class="panel-card block p-5 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-emerald-600">Completed</div>
                <span class="status-pill success">Done</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ $sessions->where('status', \App\Models\TelehealthSession::STATUS_COMPLETED)->count() }}</div>
            <p class="mt-2 text-sm text-slate-500">Completed consults</p>
        </a>

        <a href="{{ route('telehealth.index', ['view' => 'prescriptions']) }}" class="panel-card block p-5 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-indigo-600">E-Prescriptions</div>
                <span class="status-pill warning">Total</span>
            </div>
            <div class="mt-4 text-3xl font-semibold text-slate-900">{{ \App\Models\Prescription::count() }}</div>
            <p class="mt-2 text-sm text-slate-500">Total e-prescriptions</p>
        </a>
    </div>

    @if (($statusFilter ?? null) || ($prescriptionsView ?? false))
        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
            <span>
                @if (($prescriptionsView ?? false))
                    Showing generated prescriptions
                @elseif (($statusFilter ?? null) === 'live')
                    Showing live sessions only
                @else
                    Showing {{ strtoupper(($statusFilter ?? '')) }} sessions
                @endif
            </span>
            <a href="{{ route('telehealth.index') }}" class="font-medium text-teal-600 hover:text-teal-700">Clear filter</a>
        </div>
    @endif

    @if (($prescriptionsView ?? false))
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Generated prescriptions</h2>
                    <p class="text-sm text-slate-500">Patient, medication, dosage, and prescribing date.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                            <th class="py-3 px-4">Patient</th>
                            <th class="py-3 px-4">Medication</th>
                            <th class="py-3 px-4">Dosage</th>
                            <th class="py-3 px-4">Prescribed</th>
                            <th class="py-3 px-4">Session</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($prescriptions as $prescription)
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="py-3 px-4 font-medium">{{ $prescription->patient?->full_name ?? 'Unknown patient' }}</td>
                                <td class="py-3 px-4">{{ $prescription->medication_name }}</td>
                                <td class="py-3 px-4">{{ $prescription->dosage }}</td>
                                <td class="py-3 px-4">{{ $prescription->prescribed_at?->format('M d, Y g:i A') ?? '—' }}</td>
                                <td class="py-3 px-4">{{ $prescription->telehealthSession?->appointment?->appointment_number ?? '#' . $prescription->telehealth_session_id }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-slate-400">No prescription records generated yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-200">{{ $prescriptions->links() }}</div>
        </div>
    @else
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                            <th class="relative py-3 px-4 align-top">
                                <div class="flex items-center gap-2">
                                    <span>Patient</span>
                                    <button type="button" data-filter-trigger data-filter-target="telehealth-patient-filter" aria-expanded="false" class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-700" aria-label="Filter patient">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                            <path fill-rule="evenodd" d="M2.75 4.5A.75.75 0 0 1 3.5 3.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                                <div id="telehealth-patient-filter" class="filter-panel hidden absolute left-0 top-full z-20 mt-2 w-72 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                                    <form method="GET" class="space-y-3">
                                        <input type="hidden" name="date" value="{{ request('date') }}">
                                        <input type="hidden" name="status" value="{{ request('status') }}">
                                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search patient name..." class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                        <div class="flex items-center justify-end gap-2 pt-1">
                                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Apply</button>
                                        </div>
                                    </form>
                                </div>
                            </th>
                            <th class="py-3 px-4">Provider</th>
                            <th class="relative py-3 px-4 align-top">
                                <div class="flex items-center gap-2">
                                    <span>Date/Time</span>
                                    <button type="button" data-filter-trigger data-filter-target="telehealth-date-filter" aria-expanded="false" class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-700" aria-label="Filter date">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                            <path fill-rule="evenodd" d="M2.75 4.5A.75.75 0 0 1 3.5 3.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                                <div id="telehealth-date-filter" class="filter-panel hidden absolute left-0 top-full z-20 mt-2 w-64 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                                    <form method="GET" class="space-y-3">
                                        <input type="hidden" name="q" value="{{ request('q') }}">
                                        <input type="hidden" name="status" value="{{ request('status') }}">
                                        <input type="date" name="date" value="{{ request('date') }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                        <div class="flex items-center justify-end gap-2 pt-1">
                                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Apply</button>
                                        </div>
                                    </form>
                                </div>
                            </th>
                            <th class="py-3 px-4">Countdown</th>
                            <th class="py-3 px-4">Meeting</th>
                            <th class="relative py-3 px-4 align-top">
                                <div class="flex items-center gap-2">
                                    <span>Status</span>
                                    <button type="button" data-filter-trigger data-filter-target="telehealth-status-filter" aria-expanded="false" class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-700" aria-label="Filter status">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                            <path fill-rule="evenodd" d="M2.75 4.5A.75.75 0 0 1 3.5 3.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1-.75-.75Zm2.5 5.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                                <div id="telehealth-status-filter" class="filter-panel hidden absolute left-0 top-full z-20 mt-2 w-64 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                                    <form method="GET" class="space-y-3">
                                        <input type="hidden" name="q" value="{{ request('q') }}">
                                        <input type="hidden" name="date" value="{{ request('date') }}">
                                        <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                            <option value="">All statuses</option>
                                            <option value="{{ \App\Models\TelehealthSession::STATUS_SCHEDULED }}" {{ request('status') === \App\Models\TelehealthSession::STATUS_SCHEDULED ? 'selected' : '' }}>Scheduled</option>
                                            <option value="{{ \App\Models\TelehealthSession::STATUS_ACTIVE }}" {{ request('status') === \App\Models\TelehealthSession::STATUS_ACTIVE ? 'selected' : '' }}>Active</option>
                                            <option value="{{ \App\Models\TelehealthSession::STATUS_ONGOING }}" {{ request('status') === \App\Models\TelehealthSession::STATUS_ONGOING ? 'selected' : '' }}>Ongoing</option>
                                            <option value="{{ \App\Models\TelehealthSession::STATUS_COMPLETED }}" {{ request('status') === \App\Models\TelehealthSession::STATUS_COMPLETED ? 'selected' : '' }}>Completed</option>
                                            <option value="{{ \App\Models\TelehealthSession::STATUS_CANCELLED }}" {{ request('status') === \App\Models\TelehealthSession::STATUS_CANCELLED ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                        <div class="flex items-center justify-end gap-2 pt-1">
                                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Apply</button>
                                        </div>
                                    </form>
                                </div>
                            </th>
                            <th class="py-3 px-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $session)
                            @php
                                $isLive = in_array($session->status, [\App\Models\TelehealthSession::STATUS_ACTIVE, \App\Models\TelehealthSession::STATUS_ONGOING], true);
                            @endphp
                            <tr class="border-b border-slate-100 hover:bg-slate-50" data-session-id="{{ $session->id }}" data-appointment-id="{{ $session->appointment_id }}" data-patient-id="{{ $session->appointment?->patient_id ?? '' }}" data-patient-name="{{ $session->appointment?->patient?->full_name ?? '' }}" data-start-at="{{ $session->start_time?->toIso8601String() }}" data-live="{{ $isLive ? '1' : '0' }}">
                                <td class="py-3 px-4 font-medium">{{ $session->appointment->patient->full_name ?? '—' }}</td>
                                <td class="py-3 px-4">{{ $session->appointment->provider->full_name ?? '—' }}</td>
                                <td class="py-3 px-4">{{ $session->start_time?->format('M d, Y g:i A') }}</td>
                                <td class="py-3 px-4">
                                    <span class="countdown-label text-xs font-medium {{ $isLive ? 'text-emerald-600' : 'text-slate-500' }}" data-countdown="{{ $session->start_time?->toIso8601String() }}">
                                        {{ $isLive ? 'Live now' : 'Waiting' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono text-xs">{{ $session->join_url ? 'Secure room' : ($session->zoom_meeting_id ?? 'Not configured') }}</td>
                                <td class="py-3 px-4"><span class="px-2 py-1 text-xs rounded-full {{ $isLive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">{{ $session->displayStatus() }}</span></td>
                                <td class="py-3 px-4 flex gap-2">
                                    <button type="button" class="telehealth-view-session text-teal-600 text-xs font-medium" data-telehealth-detail-url="{{ route('telehealth.show', ['session' => $session, 'modal' => 1]) }}">View</button>
                                    @if ($session->join_url)
                                        <a href="{{ $session->join_url }}" target="_blank" rel="noopener noreferrer" class="join-room-link text-blue-600 text-xs font-medium {{ $isLive ? '' : 'pointer-events-none opacity-50' }}">{{ $isLive ? 'Join room' : 'Awaiting start' }}</a>
                                    @else
                                        <button type="button" class="telehealth-open-session text-xs font-medium text-blue-600" data-session-id="{{ $session->id }}">Open</button>
                                    @endif
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
    @endif
</div>

<div class="modal fade" id="telehealthDetailModal" tabindex="-1" aria-labelledby="telehealthDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-2xl">
            <div class="modal-header border-b border-slate-200 px-5 py-4">
                <div>
                    <h5 class="modal-title text-lg font-semibold text-slate-900" id="telehealthDetailModalLabel">Session details</h5>
                    <p class="mt-1 text-sm text-slate-500">Telehealth consultation</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-5 py-5" id="telehealthDetailModalBody">
                <div class="text-sm text-slate-500">Loading telehealth session details…</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        return;
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
                const isOpen = panel && !panel.classList.contains('hidden');

                closePanels();

                if (panel && !isOpen) {
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

        const detailModalEl = document.getElementById('telehealthDetailModal');
        const detailModalBody = document.getElementById('telehealthDetailModalBody');
        const detailModal = detailModalEl ? bootstrap.Modal.getOrCreateInstance(detailModalEl) : null;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const xsrfCookie = document.cookie.split('; ').find((cookie) => cookie.startsWith('XSRF-TOKEN='));
        const xsrfToken = xsrfCookie ? decodeURIComponent(xsrfCookie.split('=')[1] || '') : '';

        const getFetchHeaders = (extra = {}) => ({
            Accept: 'application/json, text/html',
            'X-Requested-With': 'XMLHttpRequest',
            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
            ...extra,
        });

        async function apiRequest(url, method = 'GET', payload = null) {
            const options = {
                method,
                credentials: 'same-origin',
                headers: getFetchHeaders(),
            };

            if (payload !== null) {
                options.body = payload instanceof FormData ? payload : JSON.stringify(payload);
                if (!(payload instanceof FormData)) {
                    options.headers['Content-Type'] = 'application/json';
                }
            }

            return window.HimsApi?.request?.(url, options) ?? Promise.reject(new Error('API client unavailable.'));
        }

        function attachTelehealthModalHandlers(root = document) {
            const meetingForms = root.querySelectorAll('.telehealth-meeting-form');
            meetingForms.forEach((form) => {
                if (form.dataset.telehealthHandlerBound === '1') {
                    return;
                }

                form.dataset.telehealthHandlerBound = '1';
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const formData = new FormData(form);
                    const sessionId = form.dataset.sessionId;
                    const actionUrl = form.dataset.action || `/telehealth/${sessionId}/create-meeting`;

                    try {
                        const response = await fetch(actionUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: getFetchHeaders({ Accept: 'application/json, text/html' }),
                            body: formData,
                        });

                        let payload = null;
                        const contentType = response.headers.get('content-type') || '';
                        if (contentType.includes('application/json')) {
                            payload = await response.json();
                        } else {
                            payload = { success: response.ok, message: 'Meeting updated.' };
                        }

                        if (!response.ok || payload?.success === false) {
                            throw new Error(payload?.message || 'Unable to update the telehealth meeting.');
                        }

                        if (window.hisToast) {
                            hisToast(payload?.message || 'Telehealth meeting updated.', 'success');
                        }

                        await loadTelehealthDetail(`/telehealth/${sessionId}?modal=1`);
                    } catch (error) {
                        if (window.hisToast) {
                            hisToast(error.message, 'danger');
                        }
                    }
                });
            });

            const closeoutForms = root.querySelectorAll('.telehealth-closeout-form');
            closeoutForms.forEach((form) => {
                if (form.dataset.telehealthHandlerBound === '1') {
                    return;
                }

                form.dataset.telehealthHandlerBound = '1';
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const sessionId = form.dataset.sessionId;
                    const payload = {
                        assessment: form.querySelector('[name="assessment"]')?.value || null,
                        plan: form.querySelector('[name="plan"]')?.value || null,
                        discharge_instructions: form.querySelector('[name="discharge_instructions"]')?.value || null,
                        clinic_note: form.querySelector('[name="clinic_note"]')?.value || null,
                    };

                    try {
                        const response = await fetch(`/api/v1/telehealth/${sessionId}/closeout`, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: getFetchHeaders({ Accept: 'application/json, text/html' }),
                            body: JSON.stringify(payload),
                        });

                        let result = null;
                        const contentType = response.headers.get('content-type') || '';
                        if (contentType.includes('application/json')) {
                            result = await response.json();
                        } else {
                            result = { success: response.ok, message: 'Closeout saved.' };
                        }

                        if (!response.ok || result?.success === false) {
                            throw new Error(result?.message || 'Unable to save the consultation closeout.');
                        }

                        if (window.hisToast) {
                            hisToast(result?.message || 'Telehealth consultation closeout saved.', 'success');
                        }

                        await loadTelehealthDetail(`/telehealth/${sessionId}?modal=1`);
                    } catch (error) {
                        if (window.hisToast) {
                            hisToast(error.message, 'danger');
                        }
                    }
                });
            });
        }

        async function loadTelehealthDetail(url) {
            if (!detailModal || !detailModalBody) {
                window.location.href = url;
                return;
            }

            detailModalBody.innerHTML = '<div class="text-sm text-slate-500">Loading telehealth session details…</div>';
            detailModal.show();

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: getFetchHeaders({ Accept: 'text/html' }),
                });

                if (!response.ok) {
                    throw new Error('Unable to load telehealth session details.');
                }

                const html = await response.text();
                detailModalBody.innerHTML = html;
                attachTelehealthModalHandlers(detailModalBody);
            } catch (error) {
                detailModalBody.innerHTML = `<div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">${error.message}</div>`;
            }
        }

        document.querySelectorAll('.telehealth-view-session').forEach((button) => {
            button.addEventListener('click', function () {
                const detailUrl = this.dataset.telehealthDetailUrl;
                if (detailUrl) {
                    loadTelehealthDetail(detailUrl);
                }
            });
        });

        document.querySelectorAll('.telehealth-open-session').forEach((button) => {
            button.addEventListener('click', function () {
                const sessionId = this.dataset.sessionId;
                if (sessionId) {
                    fetch(`/telehealth/${sessionId}?modal=1`, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: getFetchHeaders({ Accept: 'text/html' }),
                    }).then((response) => response.text()).then((html) => {
                        if (detailModal && detailModalBody) {
                            detailModalBody.innerHTML = html;
                            detailModal.show();
                        }
                    });
                }
            });
        });

        function updateCountdowns() {
            const nodes = document.querySelectorAll('[data-countdown]');
            nodes.forEach((el) => {
                const start = new Date(el.dataset.countdown);
                const now = new Date();
                const diff = start.getTime() - now.getTime();
                const row = el.closest('tr');
                const live = row && row.dataset.live === '1';

                if (live) {
                    el.textContent = 'Live now';
                    el.className = 'countdown-label text-xs font-medium text-emerald-600';
                    return;
                }

                if (diff <= 0) {
                    el.textContent = 'Starting';
                    el.className = 'countdown-label text-xs font-medium text-amber-600';
                    return;
                }

                const totalSeconds = Math.max(0, Math.floor(diff / 1000));
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;
                el.textContent = hours > 0 ? `${hours}h ${minutes}m ${seconds}s` : `${minutes}m ${seconds}s`;
                el.className = 'countdown-label text-xs font-medium text-slate-500';
            });
        }

        updateCountdowns();
        setInterval(updateCountdowns, 1000);
    })();
</script>
@endpush
@endsection
