<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FavoriteProduct\AddFavoriteRequest;
use App\Http\Resources\FavoriteProductResource;
use App\Http\Resources\ProductResource;
use App\Http\Responses\ApiResponse;
use App\Http\Services\FakeStoreApiService;
use App\Http\Services\FavoriteProductService;
use App\Http\Services\UserService;
use App\Models\FavoriteProduct;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


/**
 * @OA\Get(
 *     path="/api/my-favorites",
 *     summary="Listar favoritos do usuário autenticado",
 *     tags={"Meus Favoritos"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(...)
 * )
 *
 * @OA\Get(
 *     path="/api/users/{user}/favorites",
 *     summary="Listar favoritos de um usuário (admin)",
 *     tags={"Favoritos de Usuário"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="user",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(...)
 * )
 */
class FavorityController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private FavoriteProductService $favoriteProductService,
        private UserService $userService,
        private FakeStoreApiService $fakeStoreApiService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/my-favorites",
     *     summary="Listar favoritos do usuário autenticado",
     *     tags={"Meus Favoritos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(...)
     * )
     *
     * @OA\Get(
     *     path="/api/users/{user}/favorites",
     *     summary="Listar favoritos de um usuário (admin)",
     *     tags={"Favoritos de Usuário"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(...)
     * )
     */
    public function index(Request $request, int $userId): JsonResponse
    {
        $targetUser = $this->userService->show($userId);

        $this->authorize('viewAny', [FavoriteProduct::class, $targetUser]);

        $favorites = $this->favoriteProductService->getUserFavorites($userId);

        return ApiResponse::success(
            FavoriteProductResource::collection($favorites),
            'Produtos favoritos listados com sucesso'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/my-favorites",
     *     summary="Adicionar produto aos favoritos do usuário autenticado",
     *     tags={"Meus Favoritos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Produto adicionado aos favoritos")
     * )
     * @OA\Post(
     *     path="/api/users/{user}/favorites",
     *     summary="Adicionar produto aos favoritos de um usuário (admin)",
     *     tags={"Favoritos de Usuário"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Produto adicionado aos favoritos")
     * )
     */
    public function store(AddFavoriteRequest $request, $userId = null): JsonResponse
    {
        $userId = $userId ?? auth()->id();
        $targetUser = $this->userService->show($userId);

        $this->authorize('create', [FavoriteProduct::class, $targetUser]);

        $productId = $request->validated()['product_id'];

        $favorite = $this->favoriteProductService->addToFavorites($userId, $productId);

        return ApiResponse::success(
            new FavoriteProductResource($favorite),
            'Produto adicionado aos favoritos com sucesso',
            201
        );
    }


    /**
     * @OA\Delete(
     *     path="/api/my-favorites/{product}",
     *     summary="Remover produto dos favoritos do usuário autenticado",
     *     tags={"Meus Favoritos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Produto removido dos favoritos")
     * )
     * @OA\Delete(
     *     path="/api/users/{user}/favorites/{product}",
     *     summary="Remover produto dos favoritos de um usuário (admin)",
     *     tags={"Favoritos de Usuário"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Produto removido dos favoritos")
     * )
     */
    public function destroy(int $userId, int $productId): JsonResponse
    {
        $targetUser = $this->userService->show($userId);

        $this->authorize('deleteByUserAndProduct', [FavoriteProduct::class, $targetUser, $productId]);

        $this->favoriteProductService->removeFromFavorites($userId, $productId);

        return ApiResponse::success(
            null,
            'Produto removido dos favoritos com sucesso'
        );
    }


    /**
     * @OA\Get(
     *     path="/api/my-favorites/{product}/check",
     *     summary="Checar se produto está nos favoritos do usuário autenticado",
     *     tags={"Meus Favoritos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Status do favorito retornado")
     * )
     * @OA\Get(
     *     path="/api/users/{user}/favorites/{product}/check",
     *     summary="Checar se produto está nos favoritos de um usuário (admin)",
     *     tags={"Favoritos de Usuário"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Status do favorito retornado")
     * )
     */
    public function check(int $userId, int $productId): JsonResponse
    {
        $targetUser = $this->userService->show($userId);

        $this->authorize('check', [FavoriteProduct::class, $targetUser, $productId]);

        $isFavorite = $this->favoriteProductService->isFavorite($userId, $productId);

        return ApiResponse::success([
            'is_favorite' => $isFavorite,
            'user_id' => $userId,
            'product_id' => $productId
        ], 'Status do favorito verificado com sucesso');
    }


    /**
     * @OA\Get(
     *     path="/api/my-favorites/count",
     *     summary="Contar produtos favoritos do usuário autenticado",
     *     tags={"Meus Favoritos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Contagem de favoritos retornada")
     * )
     * @OA\Get(
     *     path="/api/users/{user}/favorites/count",
     *     summary="Contar produtos favoritos de um usuário (admin)",
     *     tags={"Favoritos de Usuário"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Contagem de favoritos retornada")
     * )
     */
    public function count(int $userId): JsonResponse
    {
        $targetUser = $this->userService->show($userId);

        $this->authorize('count', [FavoriteProduct::class, $targetUser]);

        $count = $this->favoriteProductService->countUserFavorites($userId);

        return ApiResponse::success([
            'count' => $count,
            'user_id' => $userId
        ], 'Contagem de favoritos obtida com sucesso');
    }
}
