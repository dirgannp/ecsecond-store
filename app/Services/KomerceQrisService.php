<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class KomerceQrisService
{
    public function isConfigured(): bool
    {
        return filled(config('services.komerce.qris_api_key'))
            && filled(config('services.komerce.qris_id'));
    }

    public function generate(float $amount): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Komerce QRIS is incomplete. Please set KOMERCE_QRIS_API_KEY and KOMERCE_QRIS_ID in .env.');
        }

        $response = $this->client()
            ->post('/api/v1/qrisly/generate-qris', [
                'qris_id' => (string) config('services.komerce.qris_id'),
                'amount' => max(1000, (int) round($amount)),
                'output_type' => 'string',
                'unique_amount' => filter_var(config('services.komerce.qris_unique_amount', true), FILTER_VALIDATE_BOOLEAN),
            ]);

        $payload = $response->json();

        if ($response->failed() || !($payload['success'] ?? false)) {
            throw new RuntimeException($payload['message'] ?? 'Failed to create Komerce QRIS.');
        }

        return $payload['data'] ?? [];
    }

    public function paymentStatus($historyId): array
    {
        $response = $this->client()
            ->get('/api/v1/qrisly/payment-status/' . urlencode((string) $historyId));

        $payload = $response->json();

        if ($response->failed()) {
            throw new RuntimeException($payload['meta']['message'] ?? 'Failed to check Komerce QRIS status.');
        }

        return $payload['data'] ?? [];
    }

    protected function client()
    {
        return Http::baseUrl(rtrim((string) config('services.komerce.qris_base_url'), '/'))
            ->acceptJson()
            ->withHeaders([
                'X-API-Key' => (string) config('services.komerce.qris_api_key'),
            ])
            ->timeout(20);
    }
}
