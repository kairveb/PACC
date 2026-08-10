<?php

namespace App\Http\Controllers;

use App\Models\TelehealthSession;
use App\Services\TelehealthService;
use App\Services\ZoomService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TelehealthController extends Controller
{
    public function __construct(protected TelehealthService $telehealth)
    {
    }

    public function index(Request $request)
    {
        $query = TelehealthSession::with(['appointment.patient', 'appointment.provider'])->orderBy('start_time', 'desc');

        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        $sessions = $query->paginate(15);

        return view('telehealth.index', ['sessions' => $sessions, 'zoomEnabled' => config('services.zoom.enabled')]);
    }

    public function show(TelehealthSession $session)
    {
        $session->load(['appointment.patient', 'appointment.provider', 'participants.user']);
        return view('telehealth.show', [
            'session' => $session,
            'zoomEnabled' => config('services.zoom.enabled'),
        ]);
    }

public function join(TelehealthSession $session)
    {
        // Patients get join_url, providers/hosts get host access
        $this->authorize('view', $session->appointment);

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

        if (!$this->telehealth->isConfigured()) {
            return back()->with('warning', 'Zoom is not configured. Session details saved locally. Enable ZOOM_ENABLED and credentials to create a live meeting.');
        }

        $meeting = $this->telehealth->createSession($session->appointment);

        return back()->with('success', 'Zoom meeting created successfully.');
    }
}
