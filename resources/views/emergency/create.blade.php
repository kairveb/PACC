@extends('layouts.hims')

@section('title', 'ER Intake')
@section('page-kicker', 'Emergency')
@section('page-title', 'ER Intake')
@section('page-badge', 'ER intake')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    @include('emergency._workflow-steps', ['currentStep' => 2])

    <div class="panel-card p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">ER intake</h2>
                <p class="mt-1 text-sm text-slate-600">Review the patient arrival details and record the essential information needed to move the case into the ER queue.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700" data-bs-toggle="modal" data-bs-target="#erIntakeModal">Open ER intake form</button>
                <a href="{{ route('emergency.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Back to ER queue</a>
            </div>
        </div>
    </div>

    @if (!empty($prefill['checkin_summary']))
        <div class="panel-card p-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-slate-900">Pre-arrival check-in summary</h3>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">Checked in via token</span>
            </div>

            <dl class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Patient</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $prefill['checkin_summary']['patient_name'] }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Age</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $prefill['checkin_summary']['age'] }} years</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Contact</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $prefill['checkin_summary']['contact_phone'] }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Address</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $prefill['checkin_summary']['address'] }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-2">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Medical history</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $prefill['checkin_summary']['medical_history'] ?: 'Not provided' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-2">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Allergies</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $prefill['checkin_summary']['allergies'] ?: 'Not provided' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-2">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Current medications</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $prefill['checkin_summary']['current_medications'] ?: 'Not provided' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-2">
                    <dt class="text-xs uppercase tracking-[0.2em] text-slate-500">Emergency contact</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $prefill['checkin_summary']['emergency_contact'] ?: 'Not provided' }}</dd>
                </div>
            </dl>
        </div>
    @endif

</div>

<div class="modal fade" id="erIntakeModal" tabindex="-1" aria-labelledby="erIntakeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl">
            <div class="modal-header border-b border-slate-200 px-5 py-4">
                <div>
                    <h5 class="modal-title text-lg font-semibold text-slate-900" id="erIntakeModalLabel">ER intake</h5>
                    <p class="mt-1 text-sm text-slate-500">Step 2 of 3 · Visit details</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-5 py-5">
                @include('emergency.partials.intake-form')
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const modalEl = document.getElementById('erIntakeModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        const showAutoOpenModal = () => {
            if (modalEl.dataset.autoOpen === 'true') {
                requestAnimationFrame(() => {
                    modal.show();
                });
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', showAutoOpenModal, { once: true });
        } else {
            showAutoOpenModal();
        }
    })();
</script>
@endpush
@endsection
