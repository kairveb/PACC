@extends('layouts.hims')

@section('title', 'Join Telehealth Consultation')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-teal-600">Virtual consultation</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Telehealth room</h1>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Live session
            </span>
            <span class="text-sm text-slate-500">{{ $session->start_time?->format('M d, Y g:i A') }}</span>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_390px]">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Room</p>
                    <h2 class="mt-1 text-base font-semibold text-slate-800">{{ $session->appointment->appointment_number ?? 'Consultation' }}</h2>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:border-slate-300">Mute</button>
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:border-slate-300">Camera</button>
                </div>
            </div>

            <div class="relative h-[520px] bg-slate-950">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(20,184,166,0.22),_transparent_60%),linear-gradient(135deg,#0f172a_0%,#111827_100%)]"></div>

                <div class="absolute left-5 top-5 z-10 flex items-center gap-3 rounded-xl border border-white/10 bg-slate-900/60 px-3 py-2 backdrop-blur-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-500/20 text-sm font-semibold text-teal-200">
                        {{ strtoupper(substr($session->appointment->patient->full_name ?? 'P', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">{{ $session->appointment->patient->full_name ?? 'Patient' }}</p>
                        <p class="text-[11px] text-slate-300">Visible to provider</p>
                    </div>
                </div>

                <div class="absolute bottom-5 right-5 z-10 flex w-56 flex-col gap-2 rounded-2xl border border-white/10 bg-slate-900/70 p-3 text-sm text-slate-100 backdrop-blur-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-300">Provider</span>
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-emerald-300">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            Online
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-500/20 text-xs font-semibold text-indigo-200">
                            {{ strtoupper(substr($session->appointment->provider->full_name ?? 'D', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-white">{{ $session->appointment->provider->full_name ?? 'Doctor' }}</p>
                            <p class="text-[11px] text-slate-400">{{ $session->appointment->provider->specialty->name ?? 'Clinician' }}</p>
                        </div>
                    </div>
                </div>

                <div class="absolute bottom-5 left-5 z-10 flex items-center gap-2 rounded-full border border-white/10 bg-slate-900/60 px-3 py-2 text-xs font-medium text-slate-200 backdrop-blur-sm">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    {{ $session->status }}
                </div>
            </div>

            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="status-toggle rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm transition hover:border-teal-300 hover:text-teal-700" data-status="Ready">Ready</button>
                    <button type="button" class="status-toggle rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm transition hover:border-teal-300 hover:text-teal-700" data-status="In session">In session</button>
                    <button type="button" class="status-toggle rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm transition hover:border-teal-300 hover:text-teal-700" data-status="Break">Break</button>
                    <button type="button" class="status-toggle rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm transition hover:border-teal-300 hover:text-teal-700" data-status="Wrap-up">Wrap-up</button>
                </div>
            </div>
        </section>

        <aside class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
                <div class="flex items-center gap-2">
                    <button type="button" class="drawer-tab active rounded-lg bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 shadow-sm" data-tab="notes">Notes</button>
                    <button type="button" class="drawer-tab rounded-lg bg-transparent px-2.5 py-1.5 text-xs font-medium text-slate-500" data-tab="chat">Chat</button>
                    <button type="button" class="drawer-tab rounded-lg bg-transparent px-2.5 py-1.5 text-xs font-medium text-slate-500" data-tab="summary">Summary</button>
                </div>
                <button type="button" class="drawer-toggle rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-medium text-slate-600">Collapse</button>
            </div>

            <div class="drawer-panel space-y-4 p-4" data-panel="notes">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="mb-2 flex items-center justify-between text-xs font-medium text-slate-600">
                        <span>Visit snapshot</span>
                        <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-indigo-700">Updated</span>
                    </div>
                    <dl class="grid grid-cols-2 gap-2 text-xs text-slate-600">
                        <div>
                            <dt class="text-slate-400">Reason</dt>
                            <dd class="mt-1 font-medium text-slate-700">Follow-up review</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Pain</dt>
                            <dd class="mt-1 font-medium text-slate-700">3/10</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">BP</dt>
                            <dd class="mt-1 font-medium text-slate-700">118/76</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Temp</dt>
                            <dd class="mt-1 font-medium text-slate-700">36.8°C</dd>
                        </div>
                    </dl>
                </div>

                <label class="block text-sm font-medium text-slate-700" for="clinical-note">Clinical note</label>
                <textarea id="clinical-note" rows="10" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 shadow-inner outline-none transition focus:border-teal-400 focus:bg-white" placeholder="Document assessment, observations, and plan...">Patient reports improved mobility and reduced discomfort. No new red flags. Continue home exercise plan and monitor hydration levels.</textarea>

                <div class="flex items-center justify-between gap-3">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:border-slate-300">Save draft</button>
                    <button type="button" class="rounded-lg bg-teal-600 px-3 py-2 text-sm font-semibold text-white hover:bg-teal-700">Finalize note</button>
                </div>
            </div>

            <div class="drawer-panel hidden space-y-3 p-4" data-panel="chat">
                <div class="space-y-3">
                    <div class="max-w-[80%] rounded-2xl rounded-bl-md bg-slate-100 px-3 py-2 text-sm text-slate-700">
                        Good morning. I’m feeling much better since the last review.
                    </div>
                    <div class="ml-auto max-w-[80%] rounded-2xl rounded-br-md bg-teal-600 px-3 py-2 text-sm text-white">
                        Excellent. Let’s keep the same plan and recheck in one week.
                    </div>
                    <div class="max-w-[80%] rounded-2xl rounded-bl-md bg-slate-100 px-3 py-2 text-sm text-slate-700">
                        Thank you. I’ll continue the exercises and hydration plan.
                    </div>
                </div>

                <div class="mt-auto flex items-center gap-2 pt-2">
                    <input type="text" value="" placeholder="Type a message..." class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-teal-400 focus:bg-white">
                    <button type="button" class="rounded-xl bg-teal-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">Send</button>
                </div>
            </div>

            <div class="drawer-panel hidden space-y-4 p-4" data-panel="summary">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Summary</p>
                    <ul class="mt-3 space-y-2 text-sm text-slate-700">
                        <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-teal-500"></span> Patient reports improved symptoms and stable vitals.</li>
                        <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-teal-500"></span> Continue follow-up care plan and mild activity progression.</li>
                        <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-teal-500"></span> Schedule next review in 7 days.</li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>

    <div class="flex flex-col justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:flex-row md:items-center">
        <div class="flex items-center gap-3 text-sm text-slate-600">
            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Secure session
            </span>
            <span>{{ $session->appointment->patient->full_name ?? 'Patient' }} · {{ $session->appointment->provider->full_name ?? 'Provider' }}</span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ $session->join_url ?? '#' }}" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:border-slate-300">
                Open external room
            </a>
            <button type="button" class="rounded-xl bg-rose-600 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-700">End consult</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const tabs = document.querySelectorAll('.drawer-tab');
        const panels = document.querySelectorAll('.drawer-panel');
        const statusButtons = document.querySelectorAll('.status-toggle');
        const drawerToggle = document.querySelector('.drawer-toggle');
        const drawerPanelRoot = document.querySelector('aside');

        function setActiveTab(tabName) {
            tabs.forEach((tab) => {
                const isActive = tab.dataset.tab === tabName;
                tab.classList.toggle('active', isActive);
                tab.classList.toggle('bg-white', isActive);
                tab.classList.toggle('shadow-sm', isActive);
                tab.classList.toggle('text-slate-700', isActive);
                tab.classList.toggle('text-slate-500', !isActive);
                tab.classList.toggle('bg-transparent', !isActive);
            });

            panels.forEach((panel) => {
                const visible = panel.dataset.panel === tabName;
                panel.classList.toggle('hidden', !visible);
            });
        }

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => setActiveTab(tab.dataset.tab));
        });

        statusButtons.forEach((button) => {
            button.addEventListener('click', () => {
                statusButtons.forEach((item) => {
                    item.classList.remove('bg-teal-600', 'text-white', 'border-teal-600');
                    item.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
                });

                button.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
                button.classList.add('bg-teal-600', 'text-white', 'border-teal-600');
            });
        });

        if (drawerToggle && drawerPanelRoot) {
            drawerToggle.addEventListener('click', () => {
                drawerPanelRoot.classList.toggle('hidden');
                drawerToggle.textContent = drawerPanelRoot.classList.contains('hidden') ? 'Expand' : 'Collapse';
            });
        }
    })();
</script>
@endpush
@endsection
