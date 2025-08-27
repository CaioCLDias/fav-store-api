<?php

use App\Exceptions\ExternalApiException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\UserAlreadyExistsException;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        $middleware->use([
            \Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class,
            \Illuminate\Http\Middleware\TrustHosts::class,
            \Illuminate\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            \Illuminate\Http\Middleware\TrustProxies::class,
        ]);
    })
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (Throwable $e) {
            if (!app()->environment('testing')) {
                Log::error('API Exception: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        });


        $exceptions->render(function (Throwable $e, $request) {
            // Only api routes
            if (!$request->is('api/*')) {
                return null;
            }

            // Custom Exceptions
            if ($e instanceof UserNotFoundException) {
                return ApiResponse::notFound($e->getMessage());
            }

            if ($e instanceof UserAlreadyExistsException) {
                return ApiResponse::conflict($e->getMessage());
            }

            if ($e instanceof UnauthorizedException) {
                return ApiResponse::forbidden($e->getMessage());
            }

            if ($e instanceof ValidationException) {
                return ApiResponse::validationError($e->getErrors(), $e->getMessage());
            }

            if ($e instanceof ExternalApiException) {
                return ApiResponse::error($e->getMessage(), 502);
            }

            if ($e instanceof AuthenticationException) {
                return ApiResponse::unauthorized('Token de autenticação necessário');
            }

            if ($e instanceof AuthorizationException) {
                return ApiResponse::forbidden('Você não tem permissão para esta ação');
            }

            if ($e instanceof AccessDeniedHttpException) {
                return ApiResponse::forbidden($e->getMessage());
            }
            if ($e instanceof ModelNotFoundException) {
                return ApiResponse::notFound('Recurso não encontrado');
            }

            if ($e instanceof NotFoundHttpException) {
                return ApiResponse::notFound('Endpoint não encontrado');
            }

            if ($e instanceof MethodNotAllowedHttpException) {
                return ApiResponse::error('Método HTTP não permitido', 405);
            }

            // JWT Exceptions
            if ($e instanceof TokenExpiredException) {
                return ApiResponse::unauthorized('Token expirado');
            }

            if ($e instanceof TokenInvalidException) {
                return ApiResponse::unauthorized('Token inválido');
            }

            if ($e instanceof JWTException) {
                return ApiResponse::unauthorized('Erro de autenticação JWT');
            }

            // Production Errors
            if (app()->environment('production')) {
                return ApiResponse::error('Erro interno do servidor', 500);
            }

            // Errors detailed for development
            return ApiResponse::error(
                'Erro interno: ' . $e->getMessage(),
                500,
                [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );
        });
    })->create();
