<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public const int
        HTTP_OK = 200,
        HTTP_CREATED = 201,
        HTTP_BAD_REQUEST = 400,
        HTTP_NOT_AUTHORIZED = 401,
        HTTP_FORBIDDEN = 403,
        HTTP_NOT_FOUND = 404,
        HTTP_VALIDATION_ERROR = 422,
        HTTP_TOO_MANY_REQUESTS = 429,
        HTTP_SERVER_ERROR = 500;

    /**
     * @param string $message
     * @param mixed|null $data
     * @param int $code
     * @return JsonResponse
     */
    public static function success(
        string $message = 'Success',
        mixed $data = null,
        int $code = self::HTTP_OK
    ) : JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * @param string $message
     * @param int $code
     * @param mixed|null $errors
     * @return JsonResponse
     */
    public static function error(
        string $message = 'Error',
        int $code = self::HTTP_BAD_REQUEST,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * @param mixed|null $errors
     * @param string $message
     * @return JsonResponse
     */
    public static function validationError(mixed $errors = null, string $message = 'Validation Error'): JsonResponse
    {
        return self::error($message, self::HTTP_VALIDATION_ERROR, $errors);
    }
}
