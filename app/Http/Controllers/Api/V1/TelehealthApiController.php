<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ClinicalDocument;
use App\Models\Encounter;
use App\Models\TelehealthSession;
use App\Services\EncounterService;
use App\Services\TelehealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class TelehealthApiController extends Controller
{
    public function __construct(
        protected TelehealthService $telehealthService,
        protected EncounterService $encounterService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('view-appointments'), 403, 'You are not authorized.');

        $query = TelehealthSession::with('appointment.patient', 'appointment.provider');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sessions = $query->orderByDesc('start_time')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Telehealth sessions retrieved successfully.',
            'data' => $sessions->items(),
            'meta' => ['current_page' => $sessions->currentPage(), 'last_page' => $sessions->lastPage(), 'total' => $sessions->total()],
        ]);
    }

    /**
     * Create a telehealth session for an appointment.
     */
    public function store(Request $request): JsonResponse
    {
        abort_unless(Gate::allows('create-appointments'), 403, 'You are not authorized.');

        $data = $request->validate([
            'appointment_id' => ['required', 'exists:appointments,id'],
        ]);

        $appointment = Appointment::findOrFail($data['appointment_id']);

        $session = $this->telehealthService->createSession($appointment);

        $configured = $this->telehealthService->isConfigured();

        return response()->json([
            'success' => true,
            'message' => $configured
                ? 'Telehealth session created successfully.'
                : 'Telehealth session created. Zoom is not configured; join URL will be available once configured.',
            'data' => $session,
            'integration' => [
                'zoom_enabled' => $configured,
                'status' => $configured ? 'CONFIGURED' : 'NOT_CONFIGURED',
            ],
        ], 201);
    }

    /**
     * Show a telehealth session.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $session = TelehealthSession::with('appointment.patient', 'appointment.provider', 'participants')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Telehealth session retrieved successfully.',
            'data' => $session,
        ]);
    }

    public function start(Request $request, int $id): JsonResponse
    {
        abort_unless(Gate::allows('start-telehealth'), 403, 'You are not authorized.');

        $session = TelehealthSession::findOrFail($id);
        $this->telehealthService->start($session);

        return response()->json([
            'success' => true,
            'message' => 'Telehealth session started successfully.',
            'data' => $session->fresh(),
        ]);
    }

    public function prescription(Request $request, int $id): JsonResponse
    {
        abort_unless(Gate::allows('start-telehealth'), 403, 'You are not authorized.');

        $session = TelehealthSession::with('appointment.patient')->findOrFail($id);

        $data = $request->validate([
            'medications' => ['required', 'array'],
            'medications.*' => ['string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $patient = $session->appointment?->patient;
        if (! $patient) {
            return response()->json([
                'success' => false,
                'message' => 'No patient is associated with this telehealth session.',
            ], 422);
        }

        $filename = 'telehealth/prescriptions/' . now()->format('Ymd_His') . '_' . $patient->id . '.txt';
        $content = "Prescription generated: " . now()->toDateTimeString() . PHP_EOL;
        $content .= "Patient: " . $patient->full_name . " (MRN: " . $patient->mrn . ")" . PHP_EOL;
        $content .= "Medications:" . PHP_EOL;
        foreach ($data['medications'] as $medication) {
            $content .= '- ' . $medication . PHP_EOL;
        }
        if (! empty($data['notes'])) {
            $content .= "Notes: " . $data['notes'] . PHP_EOL;
        }
        Storage::put($filename, $content);

        $document = ClinicalDocument::create([
            'patient_id' => $patient->id,
            'encounter_id' => null,
            'uploaded_by' => $request->user()?->id,
            'name' => 'Telehealth Prescription ' . $session->id,
            'path' => $filename,
            'mime_type' => 'text/plain',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Prescription created successfully.',
            'data' => $document,
        ]);
    }

    public function reminder(Request $request, int $id): JsonResponse
    {
        abort_unless(Gate::allows('start-telehealth'), 403, 'You are not authorized.');

        $session = TelehealthSession::with('appointment.patient')->findOrFail($id);

        $data = $request->validate([
            'channel' => ['nullable', 'in:email,sms'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $patient = $session->appointment?->patient;
        if (! $patient || empty($patient->email)) {
            return response()->json([
                'success' => false,
                'message' => 'This patient does not have a contact email for reminders.',
            ], 422);
        }

        $channel = $data['channel'] ?? 'email';
        $message = $data['message'] ?? 'This is your telehealth reminder. Please join your session at the scheduled time.';

        if ($channel === 'email') {
            Mail::to($patient->email)->send(new class($message, $session) extends \Illuminate\Mail\Mailable {
                public function __construct(public string $message, public TelehealthSession $session)
                {
                }

                public function build(): self
                {
                    return $this->subject('Telehealth reminder for ' . ($this->session->appointment?->appointment_number ?? 'consultation'))
                        ->html($this->message);
                }
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Reminder sent successfully.',
            'data' => [
                'channel' => $channel,
                'patient_email' => $patient->email,
                'session_id' => $session->id,
            ],
        ]);
    }

    public function closeout(Request $request, int $id): JsonResponse
    {
        abort_unless(Gate::allows('start-telehealth'), 403, 'You are not authorized.');

        $session = TelehealthSession::with('appointment.patient', 'appointment.provider')->findOrFail($id);

        $data = $request->validate([
            'assessment' => ['nullable', 'string'],
            'plan' => ['nullable', 'string'],
            'discharge_instructions' => ['nullable', 'string'],
            'clinic_note' => ['nullable', 'string'],
        ]);

        $patient = $session->appointment?->patient;
        $provider = $session->appointment?->provider;

        if (! $patient || ! $provider) {
            return response()->json([
                'success' => false,
                'message' => 'This telehealth session is missing its patient or provider information.',
            ], 422);
        }

        $summary = trim(($data['assessment'] ?? '') . PHP_EOL . ($data['plan'] ?? '') . PHP_EOL . ($data['discharge_instructions'] ?? ''));
        $encounter = Encounter::firstOrCreate([
            'appointment_id' => $session->appointment_id,
            'patient_id' => $patient->id,
        ], [
            'encounter_number' => $this->encounterService->generateNumber(),
            'provider_id' => $provider->id,
            'type' => Encounter::TYPE_TELEHEALTH,
            'started_at' => $session->start_time ?? now(),
            'status' => 'COMPLETED',
            'assessment' => $data['assessment'] ?? null,
            'plan' => $data['plan'] ?? null,
            'discharge_instructions' => $data['discharge_instructions'] ?? null,
            'chief_complaint' => $session->appointment?->appointmentType?->name ?? 'Telehealth consultation',
        ]);

        if (! empty($data['clinic_note'])) {
            $this->encounterService->addNote($encounter, $data['clinic_note'], $request->user()?->id);
        }

        if (! empty($summary)) {
            $encounter->update([
                'assessment' => $data['assessment'] ?? $encounter->assessment,
                'plan' => $data['plan'] ?? $encounter->plan,
                'discharge_instructions' => $data['discharge_instructions'] ?? $encounter->discharge_instructions,
            ]);
        }

        $this->telehealthService->end($session);

        return response()->json([
            'success' => true,
            'message' => 'Telehealth consultation closed out successfully.',
            'data' => [
                'session' => $session->fresh(),
                'encounter' => $encounter->fresh(),
            ],
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        abort_unless(Gate::allows('start-telehealth'), 403, 'You are not authorized.');

        $session = TelehealthSession::findOrFail($id);
        $this->telehealthService->cancel($session);

        return response()->json([
            'success' => true,
            'message' => 'Telehealth session cancelled successfully.',
            'data' => $session->fresh(),
        ]);
    }

    public function end(Request $request, int $id): JsonResponse
    {
        abort_unless(Gate::allows('start-telehealth'), 403, 'You are not authorized.');

        $session = TelehealthSession::findOrFail($id);
        $this->telehealthService->end($session);

        return response()->json([
            'success' => true,
            'message' => 'Telehealth session ended successfully.',
            'data' => $session->fresh(),
        ]);
    }
}
