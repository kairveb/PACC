<?php

namespace App\Services;

use App\Models\TelehealthParticipant;
use App\Models\TelehealthSession;
use Illuminate\Support\Facades\Http;

class TelehealthService
{
    public function __construct(protected ZoomService $zoom)
    {
    }

    /**
     * Create a telehealth session for an appointment.
     * Uses Zoom when configured, otherwise generates a secure local room URL.
     */
    public function createSession($appointment): TelehealthSession
    {
        $session = $this->ensureSession($appointment);

        if ($this->zoom->enabled()) {
            $this->configureZoomMeeting($session, $appointment);
        } else {
            $this->configureSecureRoom($session, $appointment);
        }

        return $session->fresh();
    }

    protected function ensureSession($appointment): TelehealthSession
    {
        return TelehealthSession::firstOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'start_time' => $appointment->starts_at ?? now(),
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

    protected function configureSecureRoom(TelehealthSession $session, $appointment): void
    {
        $token = $session->secureJoinToken();
        $joinUrl = url('/telehealth/' . $session->id . '/join?token=' . rawurlencode($token));

        $session->update([
            'join_url' => $joinUrl,
            'host_start_url' => $joinUrl,
            'status' => TelehealthSession::STATUS_SCHEDULED,
            'start_time' => $session->start_time ?? ($appointment->starts_at ?? now()),
            'duration' => $session->duration ?: $this->resolveDuration($appointment),
        ]);
    }

    public function generateJoinToken(TelehealthSession $session): string
    {
        return $session->secureJoinToken();
    }

    public function verifyJoinToken(TelehealthSession $session, ?string $token): bool
    {
        if (blank($token)) {
            return false;
        }

        return hash_equals($session->secureJoinToken(), (string) $token);
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

    public function cancel(TelehealthSession $session): TelehealthSession
    {
        $session->update(['status' => TelehealthSession::STATUS_CANCELLED]);
        return $session->fresh();
    }

    public function complete(TelehealthSession $session): TelehealthSession
    {
        $session->update(['status' => TelehealthSession::STATUS_COMPLETED]);
        return $session->fresh();
    }

    public function end(TelehealthSession $session): TelehealthSession
    {
        return $this->complete($session);
    }

    public function isConfigured(): bool
    {
        return $this->zoom->enabled();
    }
}
