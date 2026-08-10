<?php

namespace App\Services;

use App\Models\IntegrationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZapierService
{
    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = filter_var(config('services.zapier.enabled', false), FILTER_VALIDATE_BOOL);
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Trigger a Zapier webhook. Returns true if accepted.
     * Never send unnecessary clinical data. Only administrative/operational payloads.
     */
    public function trigger(string $event, array $payload = []): bool
    {
        $url = config('services.zapier.webhook_url');

        if (!$this->enabled || !$url) {
            $this->log($event, 'SKIPPED', ['reason' => 'ZAPIER_ENABLED=false']);
            return false;
        }

        try {
            $response = Http::timeout(15)->post($url, array_merge($payload, [
                'event' => $event,
                'triggered_at' => now()->toIso8601String(),
                'source' => 'hims',
            ]));

            $this->log($event, $response->successful() ? 'SUCCESS' : 'FAILED', ['status' => $response->status()]);
            return $response->successful();
        } catch (\Throwable $e) {
            $this->log($event, 'FAILED', ['error' => $e->getMessage()]);
            Log::warning('Zapier webhook failed', ['event' => $event, 'error' => $e->getMessage()]);
            return false;
        }
    }

    protected function log(string $event, string $status, array $metadata = []): void
    {
        try {
            IntegrationLog::create([
                'integration' => 'zapier',
                'event' => $event,
                'status' => $status,
                'metadata' => $metadata,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to write zapier integration log', ['error' => $e->getMessage()]);
        }
    }
}
