<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * @param  array<int|string, mixed>  $errors
     */
    public static function success(
        string $message,
        mixed $data = null,
        int $statusCode = 200,
        array $errors = []
    ): JsonResponse {
        return response()->json([
            'status' => 'success',
            'status_code' => $statusCode,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * @param  array<int|string, mixed>  $errors
     */
    public static function error(
        string $message,
        int $statusCode = 400,
        array $errors = [],
        mixed $data = null
    ): JsonResponse {
        return response()->json([
            'status' => 'error',
            'status_code' => $statusCode,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
        ], $statusCode);
    }
}
