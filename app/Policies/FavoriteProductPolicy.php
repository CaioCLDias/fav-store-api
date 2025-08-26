<?php
// app/Policies/FavoriteProductPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\FavoriteProduct;
use Illuminate\Auth\Access\Response;

class FavoriteProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, User $targetUser): Response
    {
        if ($user->is_admin) {
            return Response::allow();
        }
        return $user->id === $targetUser->id
            ? Response::allow()
            : Response::deny('Você só pode ver seus próprios favoritos.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FavoriteProduct $favoriteProduct): Response
    {
        return $user->id === $favoriteProduct->user_id
            ? Response::allow()
            : Response::deny('Você só pode ver seus próprios favoritos.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, User $targetUser): Response
    {
        return $user->id === $targetUser->id
            ? Response::allow()
            : Response::deny('Você só pode adicionar favoritos para sua própria conta.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FavoriteProduct $favoriteProduct): Response
    {
        return $user->id === $favoriteProduct->user_id
            ? Response::allow()
            : Response::deny('Você só pode modificar seus próprios favoritos.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FavoriteProduct $favoriteProduct): Response
    {
        return $user->id === $favoriteProduct->user_id
            ? Response::allow()
            : Response::deny('Você só pode remover seus próprios favoritos.');
    }

    /**
     * Determine whether the user can delete a favorite by user and product ID.
     */
    public function deleteByUserAndProduct(User $user, User $targetUser, int $productId): Response
    {
        return $user->id === $targetUser->id
            ? Response::allow()
            : Response::deny('Você só pode remover favoritos de sua própria conta.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FavoriteProduct $favoriteProduct): Response
    {
        return $user->id === $favoriteProduct->user_id
            ? Response::allow()
            : Response::deny('Você só pode restaurar seus próprios favoritos.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FavoriteProduct $favoriteProduct): Response
    {
        return $user->id === $favoriteProduct->user_id
            ? Response::allow()
            : Response::deny('Você só pode excluir permanentemente seus próprios favoritos.');
    }

    /**
     * Determine whether the user can check if a product is favorite.
     */
    public function check(User $user, User $targetUser, int $productId): Response
    {
        return $user->id === $targetUser->id
            ? Response::allow()
            : Response::deny('Você só pode verificar seus próprios favoritos.');
    }

    /**
     * Determine whether the user can count favorites.
     */
    public function count(User $user, User $targetUser): Response
    {
        return $user->id === $targetUser->id
            ? Response::allow()
            : Response::deny('Você só pode contar seus próprios favoritos.');
    }

    /**
     * Determine whether the user can manage favorites for a target user.
     */
    public function manage(User $user, User $targetUser): Response
    {
        return $user->id === $targetUser->id
            ? Response::allow()
            : Response::deny('Você só pode gerenciar seus próprios favoritos.');
    }
}
