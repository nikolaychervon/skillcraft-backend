<?php

declare(strict_types=1);

namespace App\Http;

use App\Application\Shared\Exceptions\ApiException;
use App\Application\Shared\Exceptions\Http\AccessDeniedException;
use App\Application\Shared\Exceptions\Http\NotFoundHttpException;
use App\Application\Shared\Exceptions\Http\TooManyRequestsHttpException;
use App\Application\Shared\Exceptions\Http\UnauthorizedException;
use App\Domain\User\Exceptions\Email\InvalidConfirmationLinkException;
use App\Http\Responses\ApiResponse;
use App\Support\Http\HttpCode;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as SymfonyNotFoundHttpException;
use Throwable;

final class ExceptionHandler
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->renderable(function (Throwable $e, Request $request) {
            return self::handle($e, $request);
        });
    }

    public static function handle(Throwable $e, Request $request): ?JsonResponse
    {
        if (!$request->expectsJson() && !$request->is('api/*')) {
            return null;
        }

        return match (true) {
            $e instanceof ValidationException => self::handleValidationException($e),
            $e instanceof ApiException => self::handleApiException($e),
            $e instanceof SymfonyNotFoundHttpException => self::handleNotFoundHttpException(),
            $e instanceof AuthenticationException => self::handleAuthenticationException(),
            $e instanceof ThrottleRequestsException => self::handleThrottleException(),
            $e instanceof InvalidSignatureException => self::handleInvalidSignatureException(),
            $e instanceof AccessDeniedHttpException => self::handleAccessDeniedHttpException(),
            default => self::handleInternalError(),
        };
    }

    private static function handleValidationException(ValidationException $e): JsonResponse
    {
        $errors = array_map(function ($message) {
            return $message[0];
        }, $e->errors());

        return ApiResponse::validationError($errors);
    }

    private static function handleApiException(ApiException $e): JsonResponse
    {
        return ApiResponse::error(
            message: $e->getMessage(),
            code: $e->getStatusCode(),
            errors: $e->getData(),
        );
    }

    private static function handleNotFoundHttpException(): JsonResponse
    {
        return self::handleApiException(new NotFoundHttpException);
    }

    private static function handleAuthenticationException(): JsonResponse
    {
        return self::handleApiException(new UnauthorizedException);
    }

    private static function handleThrottleException(): JsonResponse
    {
        return self::handleApiException(new TooManyRequestsHttpException);
    }

    private static function handleAccessDeniedHttpException(): JsonResponse
    {
        return self::handleApiException(new AccessDeniedException);
    }

    private static function handleInvalidSignatureException(): JsonResponse
    {
        return self::handleApiException(new InvalidConfirmationLinkException);
    }

    private static function handleInternalError(): JsonResponse
    {
        return ApiResponse::error(
            message: __('messages.internal-error'),
            code: HttpCode::InternalServerError,
        );
    }
}
