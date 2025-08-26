<?php

namespace App\Http\Services;

use App\Models\User;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\UserAlreadyExistsException;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class UserService
{
    /**
     * List all users with pagination
     *
     * @param array $filters
     * @param integer $perPage
     * @return LengthAwarePaginator
     */
    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            $query = User::query();

            if (!empty($filters['name'])) {
                $query->where('name', 'like', '%' . $filters['name'] . '%');
            }

            if (!empty($filters['email'])) {
                $query->where('email', 'like', '%' . $filters['email'] . '%');
            }

            return $query->orderBy('created_at', 'desc')->paginate($perPage);
        } catch (Throwable $e) {
            Log::error('Erro ao listar usuários: ' . $e->getMessage());
            throw new \Exception('Erro ao buscar usuários');
        }
    }

    public function listTrashed(int $perPage = 15): LengthAwarePaginator
    {
        try {
            return User::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate($perPage);
        } catch (Throwable $e) {
            Log::error('Erro ao listar usuários excluídos: ' . $e->getMessage());
            throw new \Exception('Erro ao buscar usuários excluídos');
        }
    }

    /**
     * Get a user by ID.
     *
     * @param integer $id
     * @return User
     */
    public function show(int $id): User
    {
        $user = User::find($id);

        if (!$user) {
            throw new UserNotFoundException("Usuário com ID {$id} não encontrado");
        }

        return $user;
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    public function store(array $data): User
    {
        try {
           
            if (User::where('email', $data['email'])->exists()) {
                throw new UserAlreadyExistsException(
                    "Já existe um usuário cadastrado com o e-mail {$data['email']}"
                );
            }

            DB::beginTransaction();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'is_admin' => $data['is_admin'],
                'password' => Hash::make($data['password']),
            ]);

            DB::commit();

            Log::info('Usuário criado com sucesso', ['user_id' => $user->id]);

            return $user;
        } catch (UserAlreadyExistsException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao criar usuário: ' . $e->getMessage());
            throw new \Exception('Erro interno ao criar usuário');
        }
    }

    /**
     * Update user
     *
     * @param integer $id
     * @param array $data
     * @return User
     */
    public function update(int $id, array $data): User
    {
        try {
            $user = $this->show($id);

            if (isset($data['email']) && $data['email'] !== $user->email) {
                if (User::where('email', $data['email'])->where('id', '!=', $id)->exists()) {
                    throw new UserAlreadyExistsException(
                        "Já existe outro usuário cadastrado com o e-mail {$data['email']}"
                    );
                }
            }

            DB::beginTransaction();

            $updateData = [];
            
            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }
            
            if (isset($data['email'])) {
                $updateData['email'] = $data['email'];
            }
            
            if (isset($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            if (isset($data['is_admin'])) {
                $updateData['is_admin'] = $data['is_admin'];
            }

            $user->update($updateData);

            DB::commit();

            Log::info('Usuário atualizado com sucesso', ['user_id' => $user->id]);

            return $user->fresh();
        } catch (UserNotFoundException | UserAlreadyExistsException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar usuário: ' . $e->getMessage());
            throw new \Exception('Erro interno ao atualizar usuário');
        }
    }

    /**
     * Delete user
     *
     * @param integer $id
     * @return boolean
     */
    public function destroy(int $id): bool
    {
        try {
            $user = $this->show($id);

            DB::beginTransaction();

            $user->delete(); 

            DB::commit();

            Log::info('Usuário excluído com sucesso', ['user_id' => $user->id]);

            return true;
        } catch (UserNotFoundException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao excluir usuário: ' . $e->getMessage());
            throw new \Exception('Erro interno ao excluir usuário');
        }
    }

    /**
     * Restore a soft-deleted user.
     *
     * @param integer $id
     * @return User
     */
    public function restore(int $id): User
    {
        try {
            $user = User::withTrashed()->find($id);

            if (!$user) {
                throw new UserNotFoundException("Usuário com ID {$id} não encontrado");
            }

            if (!$user->trashed()) {
                throw new ValidationException('Usuário não está excluído');
            }

            DB::beginTransaction();

            $user->restore();

            DB::commit();

            Log::info('Usuário restaurado com sucesso', ['user_id' => $user->id]);

            return $user;
        } catch (UserNotFoundException | ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao restaurar usuário: ' . $e->getMessage());
            throw new \Exception('Erro interno ao restaurar usuário');
        }
    }
}
