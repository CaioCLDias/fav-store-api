<?php
// app/Http/Responses/ApiResponse.php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
   
    /**
     * Unauthenticated response
     *
     * @param mixed $data
     * @param string $message
     * @param integer $statusCode
     * @return JsonResponse
     */
    public static function success(
        mixed $data = null,
        string $message = 'Operação realizada com sucesso',
        int $statusCode = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            if ($data instanceof LengthAwarePaginator) {
                $response['data'] = $data->items();
                $response['pagination'] = [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),
                ];
            } else {
                $response['data'] = $data;
            }
        }

        return response()->json($response, $statusCode);
    }
    
    /**
     * Internal Error response
     *
     * @param string $message
     * @param integer $statusCode
     * @param mixed $errors
     * @param mixed $data
     * @return JsonResponse
     */
    public static function error(
        string $message = 'Ocorreu um erro interno',
        int $statusCode = 500,
        mixed $errors = null,
        mixed $data = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation Error response
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    public static function validationError(
        array $errors,
        string $message = 'Dados inválidos fornecidos'
    ): JsonResponse {
        return self::error($message, 422, $errors);
    }

    /**
     * Not Found Error Response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function notFound(
        string $message = 'Recurso não encontrado'
    ): JsonResponse {
        return self::error($message, 404);
    }

    /**
     * Unauthorized Error Response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function unauthorized(
        string $message = 'Não autorizado'
    ): JsonResponse {
        return self::error($message, 401);
    }

    /**
     * Forbidden Error Response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function forbidden(
        string $message = 'Acesso negado'
    ): JsonResponse {
        return self::error($message, 403);
    }

    /**
     * Conflict Error Response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function conflict(
        string $message = 'Conflito de dados'
    ): JsonResponse {
        return self::error($message, 409);
    }
}
