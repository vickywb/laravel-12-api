<?php

namespace App\Services;

use App\Helpers\LoggerHelper;
use Midtrans\Config;
use Illuminate\Support\Facades\Http;

class MidtransService
{
    protected string $serverKey;
    protected bool $isProduction;
    protected string $baseUrl;
    protected string $clientKey;
    protected string $certPath;

    public function __construct()
    {
        $config = config('midtrans.midtrans');

        $this->isProduction = $config['isProduction'];
        $this->clientKey = $config['clientKey'];
        $this->serverKey = $config['serverKey'];
        $this->certPath = base_path('certs/cacert.pem');
        $this->baseUrl = $this->isProduction
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
    }

    public function createSnapTransaction(array $payload): array
    {
        $url = "{$this->baseUrl}/snap/v1/transactions";

        $response = Http::withOptions([
                'curl' => [
                    CURLOPT_CAINFO => $this->certPath,
                ],
            ])
            ->withBasicAuth($this->serverKey, '')
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post($url, $payload);

        if ($response->successful()) {
            return $response->json();
        }

        // Log jika terjadi error
        LoggerHelper::error('Midtrans Snap API Error', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        throw new \Exception('Failed to create Midtrans Snap transaction.');
    }
}
