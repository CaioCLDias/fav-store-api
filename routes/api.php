<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FavorityController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Requests\FavoriteProduct\AddFavoriteRequest;
use Illuminate\Support\Facades\Route;

// Public router
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth:api')->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // CRUD Clients
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/trashed', [UserController::class, 'trashed']);
        Route::get('{id}', [UserController::class, 'show']);
        Route::put('{id}', [UserController::class, 'update']);
        Route::delete('{id}', [UserController::class, 'destroy']);
        Route::post('/{user}/restore', [UserController::class, 'restore']);
    });

    // Favorites manager admins 
    Route::prefix('users/{user}/favorites')->group(function () {
        Route::get('/', [FavorityController::class, 'index']);
        Route::post('/', [FavorityController::class, 'store']);
        Route::delete('/{product}', [FavorityController::class, 'destroy']);
        Route::get('{product}/check', [FavorityController::class, 'check']);
        Route::get('/count', [FavorityController::class, 'count']);
    });

    // Products consults fakestore API
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('{id}', [ProductController::class, 'show']);
    });

    // Favorites User Products
    Route::prefix('my-favorites')->group(function () {
        Route::get('/', function () {
            $userId = auth()->id();
            return app(FavorityController::class)->index(request(), $userId);
        });

        Route::post('/', [FavorityController::class, 'store']);

        Route::delete('/{product}', function ($productId) {
            $userId = auth()->id();
            return app(FavorityController::class)->destroy($userId, $productId);
        });

        Route::get('/{product}/check', function ($productId) {
            $userId = auth()->id();
            return app(FavorityController::class)->check($userId, $productId);
        });

        Route::get('/count', function () {
            $userId = auth()->id();
            return app(FavorityController::class)->count($userId);
        });
    });
});
