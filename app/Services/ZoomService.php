<?php

namespace App\Services;

use App\Models\IntegrationLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZoomService
{
    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = $this->resolveEnabledState();
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    protected function resolveEnabledState(): bool
    {
        if (! filter_var(config('services.zoom.enabled', false), FILTER_VALIDATE_BOOL)) {
            return false;
        }

        return filled(config('services.zoom.account_id'))
            && filled(config('services.zoom.client_id'))
            && filled(config('services.zoom.client_secret'));
    }

    /**
     * Get an OAuth access token for the Zoom API.
     */
    protected function accessToken(): ?string
    {
        if (!$this->enabled) {
            return null;
        }

        return Cache::remember('zoom_access_token', 3400, function () {
            $response = Http::asForm()->withBasicAuth(
                config('services.zoom.client_id'),
                config('services.zoom.client_secret')
            )->post('https://zoom.us/oauth/token', [
                'grant_type' => 'account_credentials',
                'account_id' => config('services.zoom.account_id'),
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::warning('Zoom token request failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        });
    }

    /**
     * Create a Zoom meeting. Returns array with meeting_id, join_url, start_url.
     * When disabled, returns null (caller must handle development mode).
     */
    public function createMeeting(array $data): ?array
    {
        if (!$this->enabled) {
            $this->log('CREATE_MEETING', 'SKIPPED', ['reason' => 'ZOOM_ENABLED=false']);
            return null;
        }

        $token = $this->accessToken();
        if (!$token) {
            $this->log('CREATE_MEETING', 'FAILED', ['reason' => 'Unable to obtain access token']);
            return null;
        }

        $response = Http::withToken($token)->post('https://api.zoom.us/v2/users/me/meetings', [
            'topic' => $data['topic'] ?? 'Telehealth Consultation',
            'type' => 2,
            'start_time' => $data['start_time'] ?? now()->toIso8601String(),
            'duration' => $data['duration'] ?? 30,
            'timezone' => 'Asia/Manila',
            'settings' => [
                'host_video' => true,
                'participant_video' => true,
                'join_before_host' => false,
                'mute_upon_entry' => true,
                'waiting_room' => true,
            ],
        ]);

        if ($response->successful()) {
            $this->log('CREATE_MEETING', 'SUCCESS', ['meeting_id' => $response->json('id')]);
            return [
                'meeting_id' => (string) $response->json('id'),
                'join_url' => $response->json('join_url'),
                'start_url' => $response->json('start_url'),
            ];
        }

        $this->log('CREATE_MEETING', 'FAILED', ['status' => $response->status(), 'body' => $response->body()]);
        Log::warning('Zoom create meeting failed', ['status' => $response->status(), 'body' => $response->body()]);
        return null;
    }

    protected function log(string $event, string $status, array $metadata = []): void
    {
        try {
            IntegrationLog::create([
                'integration' => 'zoom',
                'event' => $event,
                'status' => $status,
                'metadata' => $metadata,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to write zoom integration log', ['error' => $e->getMessage()]);
        }
    }
}
