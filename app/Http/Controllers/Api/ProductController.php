<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Responses\ApiResponse;
use App\Http\Services\FakeStoreApiService;
use App\Http\Services\FavoriteProductService;
use App\Http\Services\UserService;


use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Tag(
 *     name="Produtos",
 *     description="Consulta de produtos da FakeStore API"
 * )
 */
class ProductController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private FavoriteProductService $favoriteProductService,
        private UserService $userService,
        private FakeStoreApiService $fakeStoreApiService
    ) {}



    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Listar todos os produtos da FakeStore API",
     *     tags={"Produtos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Produtos listados com sucesso"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $products = $this->fakeStoreApiService->getAllProducts();

        return ApiResponse::success(
            ProductResource::collection($products),
            'Produtos listados com sucesso'
        );
    }


    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Buscar produto da FakeStore API por ID",
     *     tags={"Produtos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do produto",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produto obtido com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Produto não encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     )
     * )
     */
    public function show(int $productId): JsonResponse
    {
        $product = $this->fakeStoreApiService->getProduct($productId);

        if ($product === null) {
            return ApiResponse::error('Produto não encontrado', 404);
        }

        return ApiResponse::success(
            new ProductResource($product),
            'Produto obtido com sucesso'
        );
    }

}
