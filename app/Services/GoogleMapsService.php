<?php

namespace App\Services;

use App\Models\IntegrationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = filter_var(config('services.google_maps.enabled', false), FILTER_VALIDATE_BOOL);
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function apiKey(): ?string
    {
        return config('services.google_maps.key');
    }

    /**
     * Geocode an address to coordinates. Returns null when disabled or on failure.
     */
    public function geocode(string $address): ?array
    {
        if (!$this->enabled) {
            $this->log('GEOCODE', 'SKIPPED', ['reason' => 'GOOGLE_MAPS_ENABLED=false']);
            return null;
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => $this->apiKey(),
        ]);

        if ($response->successful() && $response->json('status') === 'OK') {
            $result = $response->json('results')[0];
            $this->log('GEOCODE', 'SUCCESS', ['place_id' => $result['place_id'] ?? null]);
            return [
                'lat' => $result['geometry']['location']['lat'],
                'lng' => $result['geometry']['location']['lng'],
                'formatted_address' => $result['formatted_address'],
                'place_id' => $result['place_id'] ?? null,
            ];
        }

        $this->log('GEOCODE', 'FAILED', ['status' => $response->json('status')]);
        return null;
    }

    /**
     * Address autocomplete via Google Places API.
     */
    public function autocomplete(string $input): array
    {
        if (!$this->enabled) {
            return [];
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
            'input' => $input,
            'key' => $this->apiKey(),
        ]);

        if ($response->successful() && $response->json('status') === 'OK') {
            return collect($response->json('predictions'))->map(fn ($p) => [
                'description' => $p['description'],
                'place_id' => $p['place_id'],
            ])->toArray();
        }

        return [];
    }

    protected function log(string $event, string $status, array $metadata = []): void
    {
        try {
            IntegrationLog::create([
                'integration' => 'google_maps',
                'event' => $event,
                'status' => $status,
                'metadata' => $metadata,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to write maps integration log', ['error' => $e->getMessage()]);
        }
    }
}
