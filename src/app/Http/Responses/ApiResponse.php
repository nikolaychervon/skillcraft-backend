<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Support\Http\HttpCode;
use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(
        string $message = 'Success',
        mixed $data = null,
        HttpCode|int $code = HttpCode::Ok,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], self::statusCode($code));
    }

    public static function error(
        string $message = 'Error',
        HttpCode|int $code = HttpCode::BadRequest,
        mixed $errors = null,
    ): JsonResponse {
        $payload = ['success' => false, 'message' => $message];
        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, self::statusCode($code));
    }

    public static function validationError(mixed $errors = null, string $message = 'Validation Error'): JsonResponse
    {
        return self::error($message, HttpCode::ValidationError, $errors);
    }

    private static function statusCode(HttpCode|int $code): int
    {
        return $code instanceof HttpCode ? $code->value : $code;
    }
}
