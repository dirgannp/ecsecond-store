<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class RajaOngkirService
{
    public function isConfigured(): bool
    {
        return filled(config('services.rajaongkir.api_key'))
            && filled(config('services.rajaongkir.origin_id'));
    }

    public function allowedCourierCodes(): array
    {
        return collect(explode(',', (string) config('services.rajaongkir.couriers', 'lion')))
            ->map(fn ($courier) => strtolower(trim($courier)))
            ->filter()
            ->values()
            ->all();
    }

    public function searchDomesticDestinations(string $search): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Live RajaOngkir is not configured.');
        }

        $response = $this->client()
            ->get('/destination/domestic-destination', [
                'search' => $search,
                'limit' => 10,
                'offset' => 0,
            ]);

        $payload = $response->json();

        if ($response->failed()) {
            throw new RuntimeException($payload['meta']['message'] ?? $payload['message'] ?? 'Failed to search shipping destinations.');
        }

        return $payload['data'] ?? [];
    }

    public function calculateDomesticCost(int $destinationId, int $weight, ?string $courier = null): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Live RajaOngkir is not configured.');
        }

        $courier = strtolower((string) ($courier ?: implode(':', $this->allowedCourierCodes())));

        $response = $this->client()
            ->asForm()
            ->post('/calculate/domestic-cost', [
                'origin' => (int) config('services.rajaongkir.origin_id'),
                'destination' => $destinationId,
                'weight' => max(1, $weight),
                'courier' => $courier,
            ]);

        $payload = $response->json();

        if ($response->failed()) {
            throw new RuntimeException($payload['meta']['message'] ?? $payload['message'] ?? 'Failed to fetch shipping rates.');
        }

        return $payload['data'] ?? [];
    }

    public function serviceCode(?string $service): string
    {
        return strtolower(preg_replace('/\s+/', '', (string) $service));
    }

    protected function client()
    {
        return Http::baseUrl(rtrim((string) config('services.rajaongkir.base_url'), '/'))
            ->acceptJson()
            ->withHeaders([
                'key' => (string) config('services.rajaongkir.api_key'),
                'X-API-Key' => (string) config('services.rajaongkir.api_key'),
            ])
            ->timeout(20);
    }
}
