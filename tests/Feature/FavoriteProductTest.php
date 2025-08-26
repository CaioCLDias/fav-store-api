<?php
// tests/Feature/FavoriteProductTest.php

namespace Tests\Feature;

use App\Models\User;
use App\Models\FavoriteProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class FavoriteProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock da FakeStore API para testes
        Http::fake([
            'fakestoreapi.com/products/1' => Http::response([
                'id' => 1,
                'title' => 'Produto Teste',
                'price' => 109.95,
                'description' => 'Descrição do produto teste',
                'category' => 'electronics',
                'image' => 'https://fakestoreapi.com/img/81fPKd-2AYL._AC_SL1500_.jpg',
                'rating' => ['rate' => 3.9, 'count' => 120]
            ], 200),
            
            'fakestoreapi.com/products/999' => Http::response([], 404),
            
            'fakestoreapi.com/products' => Http::response([
                [
                    'id' => 1,
                    'title' => 'Produto Teste',
                    'price' => 109.95,
                    'description' => 'Descrição do produto teste',
                    'category' => 'electronics',
                    'image' => 'https://fakestoreapi.com/img/81fPKd-2AYL._AC_SL1500_.jpg',
                    'rating' => ['rate' => 3.9, 'count' => 120]
                ]
            ], 200),
        ]);
    }

    /**
     * Test user can add product to favorites.
     */
    public function test_user_can_add_product_to_favorites(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $favoriteData = [
            'product_id' => 1,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/users/{$user->id}/favorites", $favoriteData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Produto adicionado aos favoritos com sucesso'
            ]);

        $this->assertDatabaseHas('favorite_products', [
            'user_id' => $user->id,
            'product_id' => 1,
        ]);
    }

    /**
     * Test user can add product to their own favorites via my-favorites route.
     */
    public function test_user_can_add_product_via_my_favorites_route(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $favoriteData = [
            'product_id' => 1,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/my-favorites', $favoriteData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('favorite_products', [
            'user_id' => $user->id,
            'product_id' => 1,
        ]);
    }

    /**
     * Test cannot add non-existent product to favorites.
     */
    public function test_cannot_add_non_existent_product_to_favorites(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $favoriteData = [
            'product_id' => 999, // Produto que não existe na FakeStore API
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/users/{$user->id}/favorites", $favoriteData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    /**
     * Test cannot add duplicate product to favorites.
     */
    public function test_cannot_add_duplicate_product_to_favorites(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // Criar favorito existente
        FavoriteProduct::factory()->forUser($user)->forProduct(1)->create();

        $favoriteData = [
            'product_id' => 1,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/users/{$user->id}/favorites", $favoriteData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    /**
     * Test user can list their own favorites.
     */
    public function test_user_can_list_their_own_favorites(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // Criar alguns favoritos
        FavoriteProduct::factory()->forUser($user)->forProduct(1)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/users/{$user->id}/favorites");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'favorite_id',
                        'user_id',
                        'product_id',
                        'added_at',
                        'product' => [
                            'id',
                            'title',
                            'image',
                            'price',
                            'review',
                            'available'
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test user cannot list other user's favorites.
     */
    public function test_user_cannot_list_other_users_favorites(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/users/{$otherUser->id}/favorites");

        $response->assertStatus(403);
    }

    /**
     * Test admin can list any user's favorites.
     */
    public function test_admin_can_list_any_users_favorites(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($admin);

        // Criar favorito para o usuário
        FavoriteProduct::factory()->forUser($user)->forProduct(1)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/users/{$user->id}/favorites");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['favorite_id', 'user_id', 'product_id', 'product']
                ]
            ]);
    }

    /**
     * Test user can remove product from favorites.
     */
    public function test_user_can_remove_product_from_favorites(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // Criar favorito
        $favorite = FavoriteProduct::factory()->forUser($user)->forProduct(1)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/users/{$user->id}/favorites/1");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Produto removido dos favoritos com sucesso'
            ]);

        $this->assertDatabaseMissing('favorite_products', [
            'user_id' => $user->id,
            'product_id' => 1,
        ]);
    }

    /**
     * Test user can remove product via my-favorites route.
     */
    public function test_user_can_remove_product_via_my_favorites_route(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // Criar favorito
        FavoriteProduct::factory()->forUser($user)->forProduct(1)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/my-favorites/1');

        $response->assertStatus(200);

        $this->assertDatabaseMissing('favorite_products', [
            'user_id' => $user->id,
            'product_id' => 1,
        ]);
    }

    /**
     * Test cannot remove non-existent favorite.
     */
    public function test_cannot_remove_non_existent_favorite(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/users/{$user->id}/favorites/1");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    /**
     * Test user can check if product is favorite.
     */
    public function test_user_can_check_if_product_is_favorite(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // Criar favorito
        FavoriteProduct::factory()->forUser($user)->forProduct(1)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/users/{$user->id}/favorites/1/check");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_favorite' => true,
                    'product_id' => 1,
                    'user_id' => $user->id,
                ]
            ]);
    }

    /**
     * Test user can count their favorites.
     */
    public function test_user_can_count_their_favorites(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // Criar alguns favoritos
        FavoriteProduct::factory()->forUser($user)->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/users/{$user->id}/favorites/count");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'count' => 3,
                    'user_id' => $user->id,
                ]
            ]);
    }

    /**
     * Test my-favorites routes work correctly.
     */
    public function test_my_favorites_routes_work_correctly(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // Criar favorito
        FavoriteProduct::factory()->forUser($user)->forProduct(1)->create();

        // Listar meus favoritos
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/my-favorites');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));

        // Verificar se é meu favorito
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/my-favorites/1/check');

        $response->assertStatus(200)
            ->assertJson(['data' => ['is_favorite' => true]]);

        // Contar meus favoritos
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/my-favorites/count');

        $response->assertStatus(200)
            ->assertJson(['data' => ['count' => 1]]);
    }

    /**
     * Test favorites are cleaned up when product no longer exists.
     */
    public function test_favorites_are_cleaned_up_when_product_no_longer_exists(): void
    {
        // Mock produto que não existe mais
        Http::fake([
            'fakestoreapi.com/products/999' => Http::response([], 404),
        ]);

        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // Criar favorito para produto que não existe mais
        FavoriteProduct::factory()->forUser($user)->forProduct(999)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/users/{$user->id}/favorites");

        $response->assertStatus(200);

        // O favorito deve ter sido removido automaticamente
        $this->assertDatabaseMissing('favorite_products', [
            'user_id' => $user->id,
            'product_id' => 999,
        ]);
    }

    /**
     * Test product data is formatted correctly in favorites response.
     */
    public function test_product_data_is_formatted_correctly_in_favorites_response(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        FavoriteProduct::factory()->forUser($user)->forProduct(1)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/users/{$user->id}/favorites");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'product' => [
                            'id',
                            'title',
                            'image',
                            'price' => ['value', 'formatted'],
                            'review' => ['rating', 'count', 'stars'],
                            'available'
                        ]
                    ]
                ]
            ]);

        $product = $response->json('data.0.product');
        $this->assertEquals(1, $product['id']);
        $this->assertEquals('Produto Teste', $product['title']);
        $this->assertEquals('R$ 109,95', $product['price']['formatted']);
        $this->assertEquals('★★★★☆', $product['review']['stars']);
        $this->assertTrue($product['available']);
    }
}
