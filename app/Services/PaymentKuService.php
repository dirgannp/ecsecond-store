<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaymentKuService
{
    public function isConfigured(): bool
    {
        return filled(config('services.paymentku.api_key'))
            && filled(config('services.paymentku.merchant_id'));
    }

    public function generate(float $amount, ?string $orderNumber = null): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('PaymentKu is not configured. Please set PAYMENTKU_API_KEY and PAYMENTKU_MERCHANT_ID in .env.');
        }

        $payload = [
            'merchant_id' => config('services.paymentku.merchant_id'),
            'amount' => max(1000, (int) round($amount)),
            'type' => config('services.paymentku.qris_type', 'QRIS'),
        ];

        if ($orderNumber) {
            $payload['reference_id'] = $orderNumber;
        }

        if ($callbackUrl = config('services.paymentku.callback_url')) {
            $payload['callback_url'] = $callbackUrl;
        }

        $response = $this->client()
            ->post('/api/v1/qris/create', $payload);

        $result = $response->json();

        if ($response->failed() || !($result['success'] ?? false)) {
            throw new RuntimeException($result['message'] ?? 'Failed to create PaymentKu QRIS.');
        }

        return [
            'transaction_id' => $result['data']['transaction_id'] ?? $result['data']['id'] ?? null,
            'qris_string' => $result['data']['qris_string'] ?? $result['data']['qris'] ?? null,
            'original_amount' => $result['data']['original_amount'] ?? $amount,
            'final_amount' => $result['data']['final_amount'] ?? $result['data']['amount'] ?? $amount,
            'expiry_time' => $result['data']['expiry_time'] ?? $result['data']['expired_at'] ?? null,
            'qris_image' => $result['data']['qris_image'] ?? $result['data']['image_url'] ?? null,
        ];
    }

    public function paymentStatus(string $transactionId): array
    {
        $response = $this->client()
            ->get('/api/v1/qris/status/' . urlencode($transactionId));

        $result = $response->json();

        if ($response->failed()) {
            throw new RuntimeException($result['message'] ?? 'Failed to check PaymentKu status.');
        }

        return $result['data'] ?? [];
    }

    protected function client()
    {
        return Http::baseUrl(rtrim((string) config('services.paymentku.base_url'), '/'))
            ->acceptJson()
            ->withHeaders([
                'Authorization' => 'Bearer ' . config('services.paymentku.api_key'),
                'X-API-Key' => config('services.paymentku.api_key'),
            ])
            ->timeout(20);
    }
}
