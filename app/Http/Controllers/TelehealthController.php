<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\TelehealthSession;
use App\Services\TelehealthService;
use App\Services\ZoomService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TelehealthController extends Controller
{
    public function __construct(protected TelehealthService $telehealth)
    {
    }

    public function index(Request $request)
    {
        $statusFilter = $request->get('status');
        $dateFilter = $request->get('date');
        $searchQuery = trim((string) $request->get('q', ''));
        $prescriptionsView = $request->input('view') === 'prescriptions';

        $query = TelehealthSession::with(['appointment.patient', 'appointment.provider'])->orderBy('start_time', 'desc');

        $user = auth()->user();
        if ($user && $user->hasRole('patient')) {
            $patientId = $user->patient?->id;
            $query->whereHas('appointment', fn ($appointmentQuery) => $appointmentQuery->where('patient_id', $patientId ?? 0));
        } elseif ($user && $user->hasRole('doctor')) {
            $providerId = $user->provider?->id;
            $query->whereHas('appointment', fn ($appointmentQuery) => $appointmentQuery->where('provider_id', $providerId ?? 0));
        }

        if ($searchQuery !== '') {
            $query->whereHas('appointment.patient', function ($patientQuery) use ($searchQuery) {
                $patientQuery->where(function ($inner) use ($searchQuery) {
                    $search = '%' . strtolower($searchQuery) . '%';
                    $inner->whereRaw('LOWER(first_name) LIKE ?', [$search])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', [$search])
                        ->orWhereRaw('LOWER(CONCAT(first_name, " ", last_name)) LIKE ?', [$search]);
                });
            });
        }

        if ($dateFilter) {
            $query->whereDate('start_time', $dateFilter);
        }

        if ($statusFilter === 'live') {
            $query->whereIn('status', [TelehealthSession::STATUS_ACTIVE, TelehealthSession::STATUS_ONGOING]);
        } elseif ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $sessions = $query->paginate(15);
        $prescriptions = Prescription::with(['patient', 'telehealthSession.appointment.patient'])
            ->orderByDesc('prescribed_at')
            ->paginate(15, ['*'], 'prescriptions_page');

        return view('telehealth.index', [
            'sessions' => $sessions,
            'prescriptions' => $prescriptions,
            'zoomEnabled' => config('services.zoom.enabled'),
            'statusFilter' => $statusFilter,
            'dateFilter' => $dateFilter,
            'searchQuery' => $searchQuery,
            'prescriptionsView' => $prescriptionsView,
        ]);
    }

    public function show(TelehealthSession $session, Request $request)
    {
        $session->load(['appointment.patient', 'appointment.provider', 'participants.user']);

        if ($request->boolean('modal') || $request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return view('telehealth.modal-detail', [
                'session' => $session,
                'zoomEnabled' => config('services.zoom.enabled'),
            ]);
        }

        return view('telehealth.show', [
            'session' => $session,
            'zoomEnabled' => config('services.zoom.enabled'),
        ]);
    }

public function join(TelehealthSession $session, Request $request)
    {
        $user = auth()->user();

        $this->authorize('view', $session->appointment);

        $token = $request->query('token');
        abort_unless($this->telehealth->verifyJoinToken($session, $token), 403, 'Invalid or expired telehealth session link.');
        abort_unless(Gate::allows('join-telehealth'), 403, 'You are not authorized to join this telehealth room.');

        if ($user && $user->hasRole('patient')) {
            $sessionPatientId = $session->appointment?->patient_id;
            $patientUserId = $user->patient?->id;

            if ($patientUserId === null || $sessionPatientId !== $patientUserId) {
                abort(403, 'You can only join your own telehealth session.');
            }
        }

        return view('telehealth.join', compact('session'));
    }

    public function createMeeting(Request $request, TelehealthSession $session)
    {
        $data = $request->validate([
            'start_time' => ['nullable', 'date'],
            'duration' => ['nullable', 'integer', 'min:5', 'max:240'],
        ]);

        $session->update([
            'start_time' => $data['start_time'] ? Carbon::parse($data['start_time']) : $session->start_time,
            'duration' => $data['duration'] ?? $session->duration,
        ]);

        if (! $this->telehealth->isConfigured()) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Zoom is not configured. Session details saved locally. Enable ZOOM_ENABLED and credentials to create a live meeting.',
                    'data' => $session->fresh(),
                ]);
            }

            return back()->with('warning', 'Zoom is not configured. Session details saved locally. Enable ZOOM_ENABLED and credentials to create a live meeting.');
        }

        $this->telehealth->createSession($session->appointment);

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Zoom meeting created successfully.',
                'data' => $session->fresh(),
            ]);
        }

        return back()->with('success', 'Zoom meeting created successfully.');
    }
}
