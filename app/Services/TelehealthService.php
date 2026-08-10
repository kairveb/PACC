<?php

namespace App\Services;

use App\Models\TelehealthParticipant;
use App\Models\TelehealthSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelehealthService
{
    public function __construct(protected ZoomService $zoom)
    {
    }

    /**
     * Create a telehealth session for an appointment.
     * Uses Zoom when configured, otherwise marks as NOT_CONFIGURED.
     */
    public function createSession($appointment): TelehealthSession
    {
        $session = $this->ensureSession($appointment);

        if ($this->zoom->enabled()) {
            $this->configureZoomMeeting($session, $appointment);
        } elseif ($this->hasDailyConfig()) {
            $this->configureDailyRoom($session, $appointment);
        }

        return $session;
    }

    protected function ensureSession($appointment): TelehealthSession
    {
        return TelehealthSession::firstOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'start_time' => $appointment->starts_at,
                'duration' => $this->resolveDuration($appointment),
                'status' => TelehealthSession::STATUS_NOT_CONFIGURED,
            ]
        );
    }

    protected function resolveDuration($appointment): int
    {
        return $appointment->starts_at && $appointment->ends_at
            ? (int) $appointment->starts_at->diffInMinutes($appointment->ends_at)
            : 30;
    }

    protected function configureZoomMeeting(TelehealthSession $session, $appointment): void
    {
        $meeting = $this->zoom->createMeeting([
            'topic' => 'Telehealth Consultation - ' . $appointment->appointment_number,
            'start_time' => $appointment->starts_at?->toIso8601String(),
            'duration' => $session->duration,
        ]);

        if ($meeting) {
            $session->update([
                'zoom_meeting_id' => $meeting['meeting_id'],
                'join_url' => $meeting['join_url'],
                'host_start_url' => $meeting['start_url'],
                'status' => TelehealthSession::STATUS_SCHEDULED,
            ]);
        }
    }

    protected function hasDailyConfig(): bool
    {
        return filled(config('services.daily.api_key'));
    }

    protected function configureDailyRoom(TelehealthSession $session, $appointment): void
    {
        $response = Http::withToken(config('services.daily.api_key'))
            ->post('https://api.daily.co/v1/rooms', [
                'name' => 'consult-' . Str::random(8),
                'properties' => [
                    'exp' => now()->addHours(2)->timestamp,
                    'enable_chat' => true,
                ],
            ]);

        if ($response->successful()) {
            $room = $response->json();
            $session->update([
                'join_url' => $room['url'] ?? null,
                'host_start_url' => $room['url'] ?? null,
                'status' => TelehealthSession::STATUS_ACTIVE,
            ]);
        }
    }

    /**
     * Add a participant (patient or provider) to a telehealth session.
     */
    public function addParticipant(TelehealthSession $session, ?int $userId, string $role): TelehealthParticipant
    {
        return TelehealthParticipant::firstOrCreate(
            ['telehealth_session_id' => $session->id, 'user_id' => $userId, 'role' => $role]
        );
    }

    public function join(TelehealthSession $session, ?int $userId, string $role): TelehealthSession
    {
        $this->addParticipant($session, $userId, $role);
        $session->update(['status' => TelehealthSession::STATUS_ACTIVE]);
        return $session;
    }

    public function start(TelehealthSession $session): TelehealthSession
    {
        $session->update([
            'status' => TelehealthSession::STATUS_ACTIVE,
            'start_time' => $session->start_time ?? now(),
        ]);

        return $session->fresh();
    }

    public function complete(TelehealthSession $session): TelehealthSession
    {
        $session->update(['status' => TelehealthSession::STATUS_COMPLETED]);
        return $session;
    }

    public function end(TelehealthSession $session): TelehealthSession
    {
        $session->update(['status' => TelehealthSession::STATUS_COMPLETED]);
        return $session;
    }

    public function isConfigured(): bool
    {
        return $this->zoom->enabled();
    }
}
