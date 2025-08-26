<?php
// app/Services/FakeStoreApiService.php

namespace App\Http\Services;

use App\Exceptions\ExternalApiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class FakeStoreApiService
{
    private string $baseUrl;
    private int $timeout;
    private int $maxRetries;
    private int $cacheTtl;
    private string $rateLimitKey;
    private int $rateLimitMax;

    public function __construct()
    {
        $this->baseUrl = env('FAKESTORE_API_URL', 'https://fakestoreapi.com');
        $this->timeout = env('FAKESTORE_API_TIMEOUT', 10);
        $this->maxRetries = env('FAKESTORE_API_MAX_RETRIES', 3);
        $this->cacheTtl = env('FAKESTORE_API_CACHE_TTL', 3600);
        $this->rateLimitKey = 'fakestore_api_requests';
        $this->rateLimitMax = env('FAKESTORE_API_RATE_LIMIT_MAX', 100);
    }

    private function makeRequest(string $endpoint): array
    {
        // Rate limiting
        if (RateLimiter::tooManyAttempts($this->rateLimitKey, $this->rateLimitMax)) {
            $seconds = RateLimiter::availableIn($this->rateLimitKey);
            throw new ExternalApiException("Rate limit excedido. Tente novamente em {$seconds} segundos.");
        }

        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                RateLimiter::hit($this->rateLimitKey, 60);

                $response = Http::timeout($this->timeout)
                    ->get($this->baseUrl . $endpoint);

                if ($response->successful()) {
                    Log::info('Requisição à FakeStore API bem-sucedida', [
                        'endpoint' => $endpoint,
                        'attempt' => $attempt + 1
                    ]);

                    return $response->json();
                }

                if ($response->status() === 404) {
                    return []; // Produto não encontrado
                }

                throw new ExternalApiException(
                    'Erro na FakeStore API: HTTP ' . $response->status()
                );
            } catch (Throwable $e) {
                $lastException = $e;
                $attempt++;

                Log::warning('Tentativa de requisição falhou', [
                    'endpoint' => $endpoint,
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);

                if ($attempt < $this->maxRetries) {
                    sleep(pow(2, $attempt)); 
                }
            }
        }

        Log::error('Todas as tentativas falharam', [
            'endpoint' => $endpoint,
            'attempts' => $this->maxRetries
        ]);

        throw new ExternalApiException('Serviço de produtos temporariamente indisponível');
    }

    /**
     * Get all products
     *
     * @return array
     */
    public function getAllProducts(): array
    {
        $cacheKey = 'fakestore_all_products';

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            $products = $this->makeRequest('/products');

            Log::info('Todos os produtos buscados da FakeStore API', [
                'count' => count($products)
            ]);

            return $products;
        });
    }


    /**
     * Get product by ID
     *
     * @param integer $productId
     * @return array|null
     */
    public function getProduct(int $productId): ?array
    {
        $cacheKey = "fakestore_product_{$productId}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($productId) {
            $product = $this->makeRequest("/products/{$productId}");

            if (empty($product)) {
                Log::info('Produto não encontrado na FakeStore API', [
                    'product_id' => $productId
                ]);
                return null;
            }

            Log::info('Produto encontrado na FakeStore API', [
                'product_id' => $productId,
                'title' => $product['title'] ?? 'N/A'
            ]);

            return $product;
        });
    }

    /**
     * Check if a product exists
     *
     * @param integer $productId
     * @return boolean
     */
    public function productExists(int $productId): bool
    {
        try {
            $product = $this->getProduct($productId);
            return $product !== null;
        } catch (ExternalApiException $e) {
            Log::warning('Erro ao verificar existência do produto', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

   
    /**
     * Clear the cache for products
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget('fakestore_all_products');

        Log::info('Cache da FakeStore API limpo');
    }


    /**
     * API Stats for monitoring
     *
     * @return array
     */
    public function getStats(): array
    {
        $rateLimitRemaining = $this->rateLimitMax - RateLimiter::attempts($this->rateLimitKey);

        return [
            'base_url' => $this->baseUrl,
            'rate_limit_remaining' => max(0, $rateLimitRemaining),
            'rate_limit_max' => $this->rateLimitMax,
            'cache_ttl_seconds' => $this->cacheTtl,
            'timeout_seconds' => $this->timeout,
            'max_retries' => $this->maxRetries
        ];
    }
}
