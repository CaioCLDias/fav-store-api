<?php
// app/Services/FavoriteProductService.php

namespace App\Http\Services;

use App\Models\User;
use App\Models\FavoriteProduct;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\ValidationException;
use App\Exceptions\ExternalApiException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class FavoriteProductService
{
    public function __construct(
        private FakeStoreApiService $fakeStoreApiService
    ) {}
    /**
     * Get all favorite products for a user
     *
     * @param integer $userId
     * @return Collection
     */
    public function getUserFavorites(int $userId): Collection
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                throw new UserNotFoundException("Cliente com ID {$userId} não encontrado");
            }

            $favorites = $user->favoriteProducts()->get();


            $favoritesWithProducts = $favorites->map(function ($favorite) {
                try {
                    $productData = $this->fakeStoreApiService->getProduct($favorite->product_id);

                    if (!$productData) {
                        Log::warning('Produto não encontrado na FakeStore API, removendo dos favoritos', [
                            'user_id' => $favorite->user_id,
                            'product_id' => $favorite->product_id
                        ]);

                        $favorite->delete();
                        return null;
                    }

                    $favorite->product_data = $this->formatProductForFavorite($productData);
                    return $favorite;
                } catch (\Throwable $e) {
                    Log::error('Erro inesperado ao buscar dados do produto favorito: ' . $e->getMessage());
                    return null;
                }
            })->filter()->values();

            Log::info('Favoritos listados com sucesso', [
                'user_id' => $userId,
                'count' => $favoritesWithProducts->count()
            ]);

            return new Collection($favoritesWithProducts);
        } catch (UserNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Erro ao listar favoritos: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
            ]);
            return new Collection();
        }
    }

    /**
     * Add Favorite Product for User
     *
     * @param integer $userId
     * @param integer $productId
     * @return FavoriteProduct
     */
    public function addToFavorites(int $userId, int $productId): FavoriteProduct
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                throw new UserNotFoundException("Cliente com ID {$userId} não encontrado");
            }


            if (!$this->fakeStoreApiService->productExists($productId)) {
                throw new ValidationException(
                    'Produto não encontrado na FakeStore API',
                    ['product_id' => ['O produto especificado não existe']]
                );
            }


            $existingFavorite = FavoriteProduct::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($existingFavorite) {
                throw new ValidationException(
                    'Produto já está nos favoritos',
                    ['product_id' => ['Este produto já está na lista de favoritos do cliente']]
                );
            }

            DB::beginTransaction();

            $favorite = FavoriteProduct::create([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);

            DB::commit();

            Log::info('Produto adicionado aos favoritos', [
                'user_id' => $userId,
                'product_id' => $productId
            ]);

            return $favorite;
        } catch (UserNotFoundException | ValidationException | ExternalApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao adicionar produto aos favoritos: ' . $e->getMessage());
            throw new \Exception('Erro interno ao adicionar produto aos favoritos');
        }
    }

    /**
     * Add Favorite Product for User
     *
     * @param integer $userId
     * @param integer $productId
     * @return boolean
     */
    public function removeFromFavorites(int $userId, int $productId): bool
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                throw new UserNotFoundException("Cliente com ID {$userId} não encontrado");
            }

            $favorite = FavoriteProduct::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if (!$favorite) {
                throw new ValidationException(
                    'Produto não está nos favoritos',
                    ['product_id' => ['Este produto não está na lista de favoritos do cliente']]
                );
            }

            DB::beginTransaction();

            $favorite->delete();

            DB::commit();

            Log::info('Produto removido dos favoritos', [
                'user_id' => $userId,
                'product_id' => $productId
            ]);

            return true;
        } catch (UserNotFoundException | ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao remover produto dos favoritos: ' . $e->getMessage());
            throw new \Exception('Erro interno ao remover produto dos favoritos');
        }
    }

    /**
     * Undocumented function
     *
     * @param array $product
     * @return array
     */
    public function formatProductForFavorite(array $product): array
    {
        return [
            'id' => $product['id'],
            'title' => $product['title'],
            'image' => $product['image'],
            'price' => [
                'value' => $product['price'],
                'formatted' => 'R$ ' . number_format($product['price'], 2, ',', '.')
            ],
            'review' => [
                'rating' => $product['rating']['rate'] ?? null,
                'count' => $product['rating']['count'] ?? null,
                'stars' => $this->formatStars($product['rating']['rate'] ?? null)
            ]
        ];
    }

    /**
     * Format stars for rating
     *
     * @param float|null $rating
     * @return array|null
     */
    private function formatStars(?float $rating): ?string
    {
        if ($rating === null) {
            return null;
        }
        $fullStars = round($rating);
        $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;

        return str_repeat('★', $fullStars) .
            str_repeat('☆', $halfStar) .
            str_repeat('☆', $emptyStars);
    }

    /**
     * Validate if a product can be added to favorites
     *
     * @param integer $userId
     * @param integer $productId
     * @return boolean
     */
    public function isFavorite(int $userId, int $productId): bool
    {
        try {
            return FavoriteProduct::where('user_id', $userId)
                ->where('product_id', $productId)
                ->exists();
        } catch (Throwable $e) {
            Log::error('Erro ao verificar se produto é favorito: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Count user favorite products
     *
     * @param integer $userId
     * @return int
     */
    public function countUserFavorites(int $userId): int
    {
        try {
            return FavoriteProduct::where('user_id', $userId)->count();
        } catch (Throwable $e) {
            Log::error('Erro ao contar favoritos do usuário: ' . $e->getMessage());
            return 0;
        }
    }
}
