<?php

namespace App\Http\Services;

use App\Exceptions\UnauthorizedException;
use App\Exceptions\UserAlreadyExistsException;
use App\Exceptions\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Register a new user.
     *
     * @param array $data
     * @return User
     */
    public function register(array $data): User
    {
        try {
            $existingUser = User::withTrashed()->where('email', $data['email'])->first();

            if ($existingUser) {
                if ($existingUser->trashed()) {
                    throw new UserAlreadyExistsException(
                        'Este e-mail pertence a uma conta excluída. Entre em contato com o suporte.'
                    );
                } else {
                    throw new UserAlreadyExistsException('Este e-mail já está em uso');
                }
            }
            DB::beginTransaction();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'is_admin' => false,
            ]);


            DB::commit();

            Log::info('Usuário registrado com sucesso', [
                'user_id' => $user->id,
                'email' => $user->email,
                'is_admin' => $user->is_admin
            ]);

            return $user;
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao registrar usuário: ' . $e->getMessage());
            throw new \Exception('Erro interno ao registrar usuário');
        }
    }

    /**
     * User login.
     *
     * @param array $credentials
     * @return void
     */
    public function login(array $credentials): ?array
    {

        try {

            $user = User::withTrashed()->where('email', $credentials['email'])->first();

            if (!$user) {
                Log::warning('Tentativa de login com e-mail inexistente', [
                    'email' => $credentials['email']
                ]);
                throw new UnauthorizedException('Credenciais inválidas');
            }

            if ($user->trashed()) {
                Log::warning('Tentativa de login com usuário excluído', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                throw new UnauthorizedException('Conta desativada. Entre em contato com o suporte.');
            }


            if (!$token = JWTAuth::attempt($credentials)) {
                Log::warning('Tentativa de login com credenciais inválidas', [
                    'email' => $credentials['email']
                ]);
                throw new UnauthorizedException('Credenciais inválidas');
            }

            Log::info('Login realizado com sucesso', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return [
                'user' => $user,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ];
        } catch (JWTException $e) {
            return null;
        }
    }

    /**
     * Create a new JWT token for the user.
     *
     * @param User $user
     * @return string
     */
    public function createToken(User $user)
    {
        try {
            return JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            Log::error('Erro ao criar token JWT: ' . $e->getMessage());
            throw new \Exception('Erro interno ao gerar token de acesso');
        }
    }

    /**
     * Logout the user (invalidate the token).
     *
     * @return void
     */
    public function logout(): bool
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                throw new UnauthorizedException('Token não fornecido');
            }

            JWTAuth::invalidate($token);

            Log::info('Logout realizado com sucesso');

            return true;
        } catch (UnauthorizedException $e) {
            throw $e;
        } catch (JWTException $e) {
            Log::error('Erro JWT no logout: ' . $e->getMessage());
            return false;
        } catch (Throwable $e) {
            Log::error('Erro no logout: ' . $e->getMessage());
            return false;
        }
    }

    public function refresh(): ?string
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                throw new UnauthorizedException('Token não fornecido para refresh');
            }

            $newToken = JWTAuth::refresh($token);

            Log::info('Token renovado com sucesso');

            return $newToken;
        } catch (UnauthorizedException $e) {
            throw $e;
        } catch (JWTException $e) {
            Log::error('Erro JWT no refresh: ' . $e->getMessage());
            return null;
        } catch (Throwable $e) {
            Log::error('Erro no refresh: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the authenticated user.
     *
     * @return void
     */
    public function me(): ?User
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                throw new UnauthorizedException('Usuário não encontrado pelo token');
            }

            if ($user->trashed()) {
                throw new UnauthorizedException('Conta foi desativada');
            }

            return $user;
        } catch (UnauthorizedException $e) {
            throw $e;
        } catch (JWTException $e) {
            Log::error('Erro JWT ao obter usuário: ' . $e->getMessage());
            return null;
        } catch (Throwable $e) {
            Log::error('Erro ao obter usuário autenticado: ' . $e->getMessage());
            return null;
        }
    }
}
