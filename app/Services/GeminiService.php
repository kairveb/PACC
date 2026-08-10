<?php

namespace App\Services;

use App\Models\IntegrationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = filter_var(config('services.gemini.enabled', false), FILTER_VALIDATE_BOOL);
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Send a non-clinical assistive prompt to Gemini.
     * Data passed must be de-identified / non-sensitive administrative content.
     */
    public function assist(string $prompt, string $context = 'administrative'): ?string
    {
        if (!$this->enabled) {
            $this->log('ASSIST', 'SKIPPED', ['reason' => 'GEMINI_ENABLED=false']);
            return null;
        }

        $key = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-1.5-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}";

        $response = Http::timeout(30)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 800,
            ],
            'systemInstruction' => [
                'parts' => [
                    ['text' => 'You are an administrative assistant for a hospital information system. '
                        . 'You provide administrative assistance only. You do NOT diagnose, prescribe, '
                        . 'or make clinical decisions. Do not interpret or analyze medical data.'],
                ],
            ],
        ]);

        if ($response->successful()) {
            $text = $response->json('candidates.0.content.parts.0.text');
            $this->log('ASSIST', 'SUCCESS', ['context' => $context]);
            return $text;
        }

        $this->log('ASSIST', 'FAILED', ['status' => $response->status(), 'context' => $context]);
        Log::warning('Gemini assist failed', ['status' => $response->status(), 'body' => $response->body()]);
        return null;
    }

    protected function log(string $event, string $status, array $metadata = []): void
    {
        try {
            IntegrationLog::create([
                'integration' => 'gemini',
                'event' => $event,
                'status' => $status,
                'metadata' => $metadata,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to write gemini integration log', ['error' => $e->getMessage()]);
        }
    }
}
