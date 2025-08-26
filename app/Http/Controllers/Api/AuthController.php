<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Http\Services\AuthService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Autenticação",
 *     description="Gerenciamento de autenticação de usuários"
 * )
 */
class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Registrar novo usuário",
     *     tags={"Autenticação"},
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
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());
        $token = $this->authService->createToken($user);

        return ApiResponse::success([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ], 'Usuário registrado com sucesso', 201);
    }


    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Login do usuário",
     *     tags={"Autenticação"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="joao@email.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="E-mail ou senha incorretos"
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $loginData = $this->authService->login($credentials);

        if (!$loginData) {
            return ApiResponse::unauthorized('E-mail ou senha incorretos');
        }

        return ApiResponse::success([
            'user' => new UserResource($loginData['user']),
            'token' => $loginData['token'],
            'token_type' => $loginData['token_type'],
            'expires_in' => $loginData['expires_in']
        ], 'Login realizado com sucesso', 200);
    }

 
    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Logout do usuário",
     *     tags={"Autenticação"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout realizado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao fazer logout"
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        $success = $this->authService->logout();

        if (!$success) {
            return ApiResponse::error('Erro ao fazer logout', 500);
        }

        return ApiResponse::success(null, 'Logout realizado com sucesso');
    }

  
    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Renovar token JWT",
     *     tags={"Autenticação"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token renovado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não foi possível renovar o token"
     *     )
     * )
     */
    public function refresh(): JsonResponse
    {
        $newToken = $this->authService->refresh();

        if (!$newToken) {
            return ApiResponse::unauthorized('Não foi possível renovar o token');
        }

        return ApiResponse::success([
            'token' => $newToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ], 'Token renovado com sucesso');
    }

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     summary="Obter dados do usuário autenticado",
     *     tags={"Autenticação"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dados do usuário obtidos com sucesso"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou usuário não encontrado"
     *     )
     * )
     */
    public function me(): JsonResponse
    {
        $user = $this->authService->me();

        if (!$user) {
            return ApiResponse::unauthorized('Token inválido ou usuário não encontrado');
        }

        return ApiResponse::success([
            'user' => new UserResource($user)
        ], 'Dados do usuário obtidos com sucesso');
    }
}
