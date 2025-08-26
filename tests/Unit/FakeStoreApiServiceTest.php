<?php
// tests/Unit/FakeStoreApiServiceTest.php (Integration Test)

namespace Tests\Unit;

use App\Exceptions\ExternalApiException;
use App\Http\Services\FakeStoreApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class FakeStoreApiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FakeStoreApiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FakeStoreApiService();

        Cache::flush();
        RateLimiter::clear('fakestore_api_requests');
    }

    /**
     * Test getAllProducts returns products successfully.
     */
    public function test_get_all_products_returns_products_successfully(): void
    {
        $mockProducts = [
            [
                'id' => 1,
                'title' => 'Produto 1',
                'price' => 109.95,
                'description' => 'Descrição do produto 1',
                'category' => 'electronics',
                'image' => 'https://fakestoreapi.com/img/1.jpg',
                'rating' => ['rate' => 3.9, 'count' => 120]
            ],
            [
                'id' => 2,
                'title' => 'Produto 2',
                'price' => 22.3,
                'description' => 'Descrição do produto 2',
                'category' => 'jewelery',
                'image' => 'https://fakestoreapi.com/img/2.jpg',
                'rating' => ['rate' => 4.1, 'count' => 259]
            ]
        ];

        Http::fake([
            'fakestoreapi.com/products' => Http::response($mockProducts, 200)
        ]);

        $result = $this->service->getAllProducts();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('Produto 1', $result[0]['title']);
    }

    /**
     * Test getAllProducts caches results.
     */
    public function test_get_all_products_caches_results(): void
    {
        $mockProducts = [
            ['id' => 1, 'title' => 'Produto 1', 'price' => 109.95]
        ];

        Http::fake([
            'fakestoreapi.com/products' => Http::response($mockProducts, 200)
        ]);

        $result1 = $this->service->getAllProducts();

        $result2 = $this->service->getAllProducts();

        $this->assertEquals($result1, $result2);

        $this->assertTrue(Cache::has('fakestore_all_products'));

        Http::assertSentCount(1);
    }

    /**
     * Test getProductById returns product successfully.
     */
    public function test_get_product_by_id_returns_product_successfully(): void
    {
        $mockProduct = [
            'id' => 1,
            'title' => 'Produto Teste',
            'price' => 109.95,
            'description' => 'Descrição do produto teste',
            'category' => 'electronics',
            'image' => 'https://fakestoreapi.com/img/1.jpg',
            'rating' => ['rate' => 3.9, 'count' => 120]
        ];

        Http::fake([
            'fakestoreapi.com/products/1' => Http::response($mockProduct, 200)
        ]);

        $result = $this->service->getProduct(1);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Produto Teste', $result['title']);
        $this->assertEquals(109.95, $result['price']);
    }

    /**
     * Test getProductById caches individual products.
     */
    public function test_get_product_by_id_caches_individual_products(): void
    {
        $mockProduct = [
            'id' => 1,
            'title' => 'Produto Teste',
            'price' => 109.95
        ];

        Http::fake([
            'fakestoreapi.com/products/1' => Http::response($mockProduct, 200)
        ]);

        $result1 = $this->service->getProduct(1);

        $result2 = $this->service->getProduct(1);

        $this->assertEquals($result1, $result2);

        Http::assertSentCount(1);
    }

    /**
     * Test getProductById returns null for non-existent product.
     */
    public function test_get_product_by_id_returns_null_for_non_existent_product(): void
    {
        Http::fake([
            'fakestoreapi.com/products/999' => Http::response([], 404)
        ]);

        $result = $this->service->getProduct(999);

        $this->assertNull($result);
    }

    /**
     * Test productExists returns true for existing product.
     */
    public function test_product_exists_returns_true_for_existing_product(): void
    {
        $mockProduct = [
            'id' => 1,
            'title' => 'Produto Teste'
        ];

        Http::fake([
            'fakestoreapi.com/products/1' => Http::response($mockProduct, 200)
        ]);

        $this->assertTrue($this->service->productExists(1));
    }

    /**
     * Test productExists returns false for non-existent product.
     */
    public function test_product_exists_returns_false_for_non_existent_product(): void
    {
        Http::fake([
            'fakestoreapi.com/products/999' => Http::response([], 404)
        ]);

        $this->assertFalse($this->service->productExists(999));
    }

    /**
     * Test clearCache removes cached data.
     */
    public function test_clear_cache_removes_cached_data(): void
    {
        // Adicionar dados ao cache
        Cache::put('fakestore_all_products', ['test'], 3600);

        $this->assertTrue(Cache::has('fakestore_all_products'));

        $this->service->clearCache();

        $this->assertFalse(Cache::has('fakestore_all_products'));
    }

    /**
     * Test API respects configuration values.
     */
    public function test_api_respects_configuration_values(): void
    {
        config(['external_apis.fakestore.timeout' => 5]);
        config(['external_apis.fakestore.max_retries' => 2]);

        Http::fake([
            'fakestoreapi.com/products/1' => Http::sequence()
                ->push([], 500)
                ->push([], 500)
                ->push(['id' => 1], 200)
        ]);

        $result = $this->service->getProduct(1);

        $this->assertNotNull($result);

        Http::assertSentCount(3);
    }


    public function test_get_stats_returns_expected_keys()
    {
        $stats = $this->service->getStats();

        $this->assertArrayHasKey('base_url', $stats);
        $this->assertArrayHasKey('rate_limit_remaining', $stats);
        $this->assertArrayHasKey('rate_limit_max', $stats);
        $this->assertArrayHasKey('cache_ttl_seconds', $stats);
        $this->assertArrayHasKey('timeout_seconds', $stats);
        $this->assertArrayHasKey('max_retries', $stats);
    }
}
