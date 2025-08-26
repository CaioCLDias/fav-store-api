<?php
// app/Providers/AuthServiceProvider.php

namespace App\Providers;

use App\Models\User;
use App\Models\FavoriteProduct;
use App\Policies\UserPolicy;
use App\Policies\FavoriteProductPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        FavoriteProduct::class => FavoriteProductPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('manage-users', function (User $user) {
            return true; 
        });

        Gate::define('access-admin-panel', function (User $user) {
            return true; 
        });

        
        Gate::define('access-user-data', function (User $user, User $targetUser) {
            return $user->id === $targetUser->id;
        });

        
        Gate::define('manage-user-favorites', function (User $user, User $targetUser) {
            return $user->id === $targetUser->id;
        });
    }
}