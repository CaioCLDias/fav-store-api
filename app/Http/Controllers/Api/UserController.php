<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Http\Services\UserService;
use App\Models\User;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Tag(
 *     name="Usuários",
 *     description="Gerenciamento de usuários"
 * )
 */
class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private UserService $userService
    ) {}


    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Listar usuários",
     *     tags={"Usuários"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=false,
     *         description="Filtrar por nome",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=false,
     *         description="Filtrar por e-mail",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Itens por página (máx. 100)",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuários listados com sucesso"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $filters = $request->only(['name', 'email']);
        $perPage = min($request->get('per_page', 15), 100);

        $users = $this->userService->index($filters, $perPage);

        return ApiResponse::success(
            UserResource::collection($users),
            'Usuários listados com sucesso'
        );
    }


    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Buscar usuário por ID",
     *     tags={"Usuários"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário encontrado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->show($id);

        $this->authorize('view', $user);

        return ApiResponse::success(
            new UserResource($user),
            'Usuário encontrado com sucesso'
        );
    }

    /**
     * Create a new user
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Criar novo usuário",
     *     tags={"Usuários"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="João da Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@email.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="12345678")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário criado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     )
     * )
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $user = $this->userService->store($request->validated());

        return ApiResponse::success(
            new UserResource($user),
            'Usuário criado com sucesso',
            201
        );
    }

    /**
     * Update user
     *
     * @param UpdateUserRequest $request
     * @param integer $id
     * @return JsonResponse
     */
    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Atualizar usuário",
     *     tags={"Usuários"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="João da Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@email.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="12345678")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário atualizado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     )
     * )
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = $this->userService->update($id, $request->validated());

        $this->authorize('update', $user);

        return ApiResponse::success(
            new UserResource($user),
            'Usuário atualizado com sucesso'
        );
    }


    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Excluir usuário",
     *     tags={"Usuários"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário excluído com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $this->authorize('delete', $user);

        $this->userService->destroy($id);

        return response()->json([
            'success' => true,
            'message' => 'Usuário excluído com sucesso'
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/users/{id}/restore",
     *     summary="Restaurar usuário excluído",
     *     tags={"Usuários"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário restaurado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     )
     * )
     */
    public function restore(int $id): JsonResponse
    {
        $user = $this->userService->restore($id);

        $this->authorize('restore', $user);

        return ApiResponse::success(
            new UserResource($user),
            'Usuário restaurado com sucesso'
        );
    }


    /**
     * @OA\Get(
     *     path="/api/users/trashed",
     *     summary="Listar usuários excluídos (soft deleted)",
     *     tags={"Usuários"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Itens por página (máx. 100)",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuários excluídos listados com sucesso"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado"
     *     )
     * )
     */
    public function trashed(Request $request): JsonResponse
    {
        $this->authorize('viewTrashed', User::class);

        $perPage = $request->get('per_page', 15);

        if ($perPage > 100) {
            $perPage = 100;
        }

        $users = $this->userService->listTrashed($perPage);

        return ApiResponse::success(
            UserResource::collection($users),
            'Usuários excluídos listados com sucesso'
        );
    }
}
