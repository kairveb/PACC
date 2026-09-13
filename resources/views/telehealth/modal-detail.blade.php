<div class="space-y-5">
    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Session details</p>
                <h3 class="mt-1 text-lg font-semibold text-slate-900">{{ $session->appointment?->patient?->full_name ?? '—' }}</h3>
            </div>
            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $session->displayStatus() }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 text-sm text-slate-700">
        <div><span class="text-slate-500">Patient:</span> <span class="font-medium">{{ $session->appointment?->patient?->full_name ?? '—' }}</span></div>
        <div><span class="text-slate-500">Provider:</span> <span class="font-medium">{{ $session->appointment?->provider?->full_name ?? '—' }}</span></div>
        <div><span class="text-slate-500">Start:</span> <span class="font-medium">{{ $session->start_time?->format('M d, Y g:i A') ?? '—' }}</span></div>
        <div><span class="text-slate-500">Duration:</span> <span class="font-medium">{{ $session->duration ?? 30 }} minutes</span></div>
        <div><span class="text-slate-500">Meeting:</span> <span class="font-medium">{{ $session->join_url ? 'Secure room' : ($session->zoom_meeting_id ?? 'Not configured') }}</span></div>
        <div><span class="text-slate-500">Status:</span> <span class="font-medium">{{ $session->displayStatus() }}</span></div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <h4 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-600">Meeting Information</h4>
        @if ($session->zoom_meeting_id && $session->join_url)
            <div class="space-y-2 text-sm text-slate-700">
                <p>Meeting ID: <span class="font-mono">{{ $session->zoom_meeting_id }}</span></p>
                <p>Join URL: <a href="{{ $session->join_url }}" target="_blank" rel="noopener noreferrer" class="text-teal-600 hover:underline">{{ $session->join_url }}</a></p>
            </div>
        @else
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
                Zoom is not configured. Telehealth record tracked locally. Enable ZOOM_ENABLED and API credentials in .env to create live meetings.
            </div>
        @endif
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <h4 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-600">Create / Update Meeting</h4>
        <form class="telehealth-meeting-form space-y-3" data-session-id="{{ $session->id }}" data-session-url="{{ route('telehealth.show', ['session' => $session, 'modal' => 1]) }}">
            @csrf
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Start Date/Time</label>
                    <input type="datetime-local" name="start_time" value="{{ $session->start_time?->format('Y-m-d\TH:i') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Duration (min)</label>
                    <input type="number" name="duration" value="{{ $session->duration ?? 30 }}" min="5" max="240" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Create/Update Meeting</button>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Prescriptions</h4>
            <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700">Medication list</span>
        </div>

        <form class="telehealth-prescription-form space-y-4" data-session-id="{{ $session->id }}" data-patient-id="{{ $session->appointment?->patient_id ?? '' }}">
            @csrf
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label for="prescription-medication-{{ $session->id }}" class="mb-1 block text-sm font-medium text-slate-700">Medication</label>
                    <select id="prescription-medication-{{ $session->id }}" name="medication_name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                        <option value="">Select medication</option>
                        @foreach (['Amoxicillin', 'Ibuprofen', 'Paracetamol', 'Metformin', 'Atorvastatin', 'Omeprazole', 'Amlodipine', 'Cefalexin', 'Salbutamol', 'Losartan', 'Cetirizine', 'Azithromycin', 'Pantoprazole', 'Captopril', 'Metoprolol', 'Budesonide', 'Levothyroxine', 'Simvastatin', 'Clarithromycin', 'Doxycycline', 'Hydrochlorothiazide', 'Furosemide', 'Gliclazide', 'Prednisone', 'Vitamin D', 'Vitamin B Complex', 'Allopurinol', 'Aspirin'] as $medication)
                            <option value="{{ $medication }}">{{ $medication }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="prescription-dosage-{{ $session->id }}" class="mb-1 block text-sm font-medium text-slate-700">Dosage</label>
                    <input id="prescription-dosage-{{ $session->id }}" type="text" name="dosage" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="500mg twice daily" required>
                </div>
            </div>
            <div>
                <label for="prescription-instructions-{{ $session->id }}" class="mb-1 block text-sm font-medium text-slate-700">Instructions</label>
                <textarea id="prescription-instructions-{{ $session->id }}" name="instructions" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Take with food" required></textarea>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700" onclick="this.closest('form').reset()">Reset</button>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Save prescription</button>
            </div>
        </form>

        @php $prescriptions = $session->prescriptions()->orderByDesc('prescribed_at')->get(); @endphp
        @if ($prescriptions->isEmpty())
            <p class="mt-4 text-sm text-slate-500">No prescriptions saved for this session yet.</p>
        @else
            <div class="mt-4 space-y-3">
                @foreach ($prescriptions as $prescription)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-semibold text-slate-900">{{ $prescription->medication_name }}</p>
                            <span class="text-xs text-slate-500">{{ $prescription->prescribed_at?->format('M d, Y g:i A') ?? '—' }}</span>
                        </div>
                        <p class="mt-1 text-slate-600">{{ $prescription->dosage }}</p>
                        <p class="mt-2 text-slate-700">{{ $prescription->instructions }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Reminder</h4>
            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">Patient contact</span>
        </div>

        <form class="telehealth-reminder-form space-y-4" data-session-id="{{ $session->id }}">
            @csrf
            <div>
                <label for="reminder-channel-{{ $session->id }}" class="mb-1 block text-sm font-medium text-slate-700">Channel</label>
                <select id="reminder-channel-{{ $session->id }}" name="channel" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="email">Email</option>
                </select>
            </div>
            <div>
                <label for="reminder-message-{{ $session->id }}" class="mb-1 block text-sm font-medium text-slate-700">Message</label>
                <textarea id="reminder-message-{{ $session->id }}" name="message" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="This is your telehealth reminder...">This is your telehealth reminder. Please join your session at the scheduled time.</textarea>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Send reminder</button>
            </div>
            <div class="telehealth-reminder-feedback hidden rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"></div>
        </form>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Closeout consultation</h4>
            <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700">Clinical summary</span>
        </div>

        <form class="telehealth-closeout-form space-y-4" data-session-id="{{ $session->id }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="closeout-assessment-{{ $session->id }}" class="mb-1 block text-sm font-medium text-slate-700">Assessment</label>
                    <textarea id="closeout-assessment-{{ $session->id }}" name="assessment" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Document the patient assessment and findings."></textarea>
                </div>
                <div>
                    <label for="closeout-plan-{{ $session->id }}" class="mb-1 block text-sm font-medium text-slate-700">Plan</label>
                    <textarea id="closeout-plan-{{ $session->id }}" name="plan" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Outline treatment plan and recommended follow-up."></textarea>
                </div>
                <div>
                    <label for="closeout-discharge-{{ $session->id }}" class="mb-1 block text-sm font-medium text-slate-700">Discharge instructions</label>
                    <textarea id="closeout-discharge-{{ $session->id }}" name="discharge_instructions" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Any care instructions or self-management advice."></textarea>
                </div>
                <div>
                    <label for="closeout-note-{{ $session->id }}" class="mb-1 block text-sm font-medium text-slate-700">Clinic note</label>
                    <textarea id="closeout-note-{{ $session->id }}" name="clinic_note" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Add clinician documentation for the telehealth consultation."></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700" onclick="this.closest('form').reset()">Reset</button>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Save closeout</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const meetingForm = document.querySelector('.telehealth-meeting-form[data-session-id="{{ $session->id }}"]');
        const closeoutForm = document.querySelector('.telehealth-closeout-form[data-session-id="{{ $session->id }}"]');

        if (meetingForm) {
            meetingForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                const formData = new FormData(meetingForm);

                try {
                    const response = await window.HimsApi?.request?.(`/api/v1/telehealth/{{ $session->id }}/start`, 'POST', { start_time: formData.get('start_time'), duration: formData.get('duration') });
                    if (!response || !response.success) {
                        throw new Error('Unable to save the session update.');
                    }

                    if (window.hisToast) hisToast('Telehealth meeting updated.', 'success');
                    const sessionUrl = meetingForm.dataset.sessionUrl || '{{ route('telehealth.show', ['session' => $session, 'modal' => 1]) }}';
                    if (sessionUrl) {
                        window.dispatchEvent(new CustomEvent('telehealth:session-updated', { detail: { sessionUrl } }));
                    }
                } catch (error) {
                    if (window.hisToast) hisToast(error.message, 'danger');
                }
            });
        }

        if (closeoutForm) {
            closeoutForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                const payload = {
                    assessment: closeoutForm.querySelector('[name="assessment"]').value || null,
                    plan: closeoutForm.querySelector('[name="plan"]').value || null,
                    discharge_instructions: closeoutForm.querySelector('[name="discharge_instructions"]').value || null,
                    clinic_note: closeoutForm.querySelector('[name="clinic_note"]').value || null,
                };

                try {
                    const response = await window.HimsApi?.request?.(`/api/v1/telehealth/{{ $session->id }}/closeout`, 'POST', payload);
                    if (!response || !response.success) {
                        throw new Error(response?.message || 'Unable to save the consultation closeout.');
                    }

                    if (window.hisToast) hisToast('Telehealth consultation closeout saved.', 'success');
                    window.dispatchEvent(new CustomEvent('telehealth:session-updated', { detail: { sessionId: {{ $session->id }} } }));
                } catch (error) {
                    if (window.hisToast) hisToast(error.message, 'danger');
                }
            });
        }

        const reminderForm = document.querySelector('.telehealth-reminder-form[data-session-id="{{ $session->id }}"]');
        if (reminderForm) {
            const reminderFeedback = reminderForm.querySelector('.telehealth-reminder-feedback');

            reminderForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                const payload = {
                    channel: reminderForm.querySelector('[name="channel"]').value || 'email',
                    message: reminderForm.querySelector('[name="message"]').value || 'This is your telehealth reminder. Please join your session at the scheduled time.',
                };

                if (reminderFeedback) {
                    reminderFeedback.classList.remove('hidden', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-700', 'border-rose-200', 'bg-rose-50', 'text-rose-700');
                    reminderFeedback.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-700');
                    reminderFeedback.textContent = 'Sending reminder...';
                    reminderFeedback.classList.remove('hidden');
                }

                try {
                    const response = await window.HimsApi?.request?.(`/api/v1/telehealth/{{ $session->id }}/reminder`, 'POST', payload);
                    if (!response || !response.success) {
                        throw new Error(response?.message || 'Unable to send the reminder.');
                    }

                    if (window.hisToast) hisToast(response.message || 'Reminder sent successfully.', 'success');

                    if (reminderFeedback) {
                        reminderFeedback.classList.remove('border-slate-200', 'bg-slate-50', 'text-slate-700');
                        reminderFeedback.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
                        reminderFeedback.textContent = response.message || 'Reminder sent successfully.';
                    }
                } catch (error) {
                    if (window.hisToast) hisToast(error.message, 'danger');

                    if (reminderFeedback) {
                        reminderFeedback.classList.remove('border-slate-200', 'bg-slate-50', 'text-slate-700');
                        reminderFeedback.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-700');
                        reminderFeedback.textContent = error.message || 'Unable to send the reminder.';
                    }
                }
            });
        }

        const prescriptionForm = document.querySelector('.telehealth-prescription-form[data-session-id="{{ $session->id }}"]');
        if (prescriptionForm) {
            prescriptionForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                const payload = {
                    medication_name: prescriptionForm.querySelector('[name="medication_name"]').value,
                    dosage: prescriptionForm.querySelector('[name="dosage"]').value,
                    instructions: prescriptionForm.querySelector('[name="instructions"]').value,
                };

                if (!payload.medication_name || !payload.dosage || !payload.instructions) {
                    if (window.hisToast) {
                        hisToast('Medication, dosage, and instructions are required.', 'warning');
                    }
                    return;
                }

                try {
                    const response = await window.HimsApi?.request?.(`/api/v1/telehealth/{{ $session->id }}/prescription`, 'POST', payload);
                    if (!response || !response.success) {
                        throw new Error(response?.message || 'Unable to save the prescription.');
                    }

                    if (window.hisToast) hisToast('Prescription saved successfully.', 'success');
                    window.dispatchEvent(new CustomEvent('telehealth:session-updated', { detail: { sessionId: {{ $session->id }} } }));
                    prescriptionForm.reset();
                    const url = '{{ route('telehealth.show', ['session' => $session, 'modal' => 1]) }}';
                    const eventTarget = window.eventTarget || window;
                    if (typeof window.dispatchEvent === 'function') {
                        window.dispatchEvent(new CustomEvent('telehealth:session-updated', { detail: { sessionUrl: url } }));
                    }
                } catch (error) {
                    if (window.hisToast) hisToast(error.message, 'danger');
                }
            });
        }
    })();
</script>
