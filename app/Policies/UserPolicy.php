<?php
// app/Policies/UserPolicy.php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {

        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): Response
    {
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return $user->id === $model->id
            ? Response::allow()
            : Response::deny('Você só pode editar seus próprios dados.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): Response
    {
        if ($user->isAdmin()) {
            return $user->id !== $model->id
                ? Response::allow()
                : Response::deny('Você não pode excluir sua própria conta de administrador.');
        }

        return $user->id === $model->id
            ? Response::allow()
            : Response::deny('Você só pode excluir sua própria conta.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): Response
    {
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return $user->id === $model->id
            ? Response::allow()
            : Response::deny('Você só pode restaurar sua própria conta.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Apenas administradores podem excluir permanentemente usuários.');
    }

    /**
     * Determine whether the user can view trashed models.
     */
    public function viewTrashed(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage another user's data.
     */
    public function manage(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can access user's favorites.
     */
    public function viewFavorites(User $user, User $model): Response
    {
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return $user->id === $model->id
            ? Response::allow()
            : Response::deny('Você só pode ver seus próprios favoritos.');
    }

    /**
     * Determine whether the user can manage user's favorites.
     */
    public function manageFavorites(User $user, User $model): Response
    {
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return $user->id === $model->id
            ? Response::allow()
            : Response::deny('Você só pode gerenciar seus próprios favoritos.');
    }
}
