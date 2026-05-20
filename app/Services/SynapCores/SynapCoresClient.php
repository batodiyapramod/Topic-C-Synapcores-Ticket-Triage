<?php

namespace App\Services\SynapCores;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class SynapCoresClient
{
    protected string $baseUrl;
    protected string $username;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('services.synapcores.base_url'), '/');
        $this->username = config('services.synapcores.username', 'admin');
        $this->apiKey   = config('services.synapcores.api_key', '');
    }

    /**
     * Direct REST classification route for Community Edition
     */
    public function predict(array $features): string
    {
        try {
            $token = $this->getAuthToken();

            // COMMUNITY FIX: Post directly to the dedicated predict route
            $response = Http::withToken($token)
                ->timeout(5)
                ->connectTimeout(3)
                ->post("{$this->baseUrl}/v1/predict", [
                    'model' => 'priority_v1',
                    'features' => [
                        $features['customer_tier'] ?? 'Bronze',
                        $features['product_area']  ?? 'General',
                        $features['subject']       ?? '',
                        $features['body']          ?? ''
                    ]
                ]);

            if ($response->failed()) {
                throw new Exception('Prediction Engine Error: ' . $response->body());
            }

            // Community returns either {"prediction": "value"} or a direct string token
            return $response->json('prediction') ?? $response->json('label') ?? 'LOW';

        } catch (\Exception $e) {
            Log::error("SynapCores Predict Failure: " . $e->getMessage());
            return 'LOW'; // Safe fallback
        }
    }

    /**
     * Direct REST embedding route for Community Edition
     */
    public function getEmbeddings(string $text): array
    {
        try {
            $token = $this->getAuthToken();

            // COMMUNITY FIX: Post directly to the dedicated embeddings route
            $response = Http::withToken($token)
                ->timeout(5)
                ->connectTimeout(3)
                ->post("{$this->baseUrl}/v1/embeddings", [
                    'model' => 'text-embedding-ada-002',
                    'input' => $text
                ]);

            if ($response->failed()) {
                throw new Exception('Embedding Engine Error: ' . $response->body());
            }

            return $response->json('data.0.embedding') ?? $response->json('embedding') ?? array_fill(0, 1536, 0.0);

        } catch (\Exception $e) {
            Log::warning("SynapCores Embedding Failure: " . $e->getMessage());
            return array_fill(0, 1536, 0.0);
        }
    }

    /**
     * Caches the authentication JWT token
     */
    protected function getAuthToken(): string
    {
        return Cache::remember('synapcores_jwt_token', 300, function () {
            if (empty($this->apiKey)) {
                return '';
            }

            $response = Http::post("{$this->baseUrl}/v1/auth/login", [
                'username' => $this->username,
                'api_key'  => $this->apiKey,
            ]);

            if ($response->failed()) {
                throw new Exception("Authentication failure: " . $response->body());
            }

            return $response->json('token');
        });
    }
}
