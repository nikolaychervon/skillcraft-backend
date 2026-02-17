<?php

use App\Application\Shared\Exceptions\ApiException;
use App\Application\Shared\Exceptions\Http\NotFoundHttpException;
use App\Application\Shared\Exceptions\Http\TooManyRequestsHttpException;
use App\Application\Shared\Exceptions\Http\UnauthorizedException;
use App\Http\Middleware\ThrottleAfterValidationMiddleware;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as BaseNotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'throttle.after' => ThrottleAfterValidationMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $errors = [];
                foreach ($e->errors() as $field => $message) {
                    $errors[$field] = $message[0];
                }

                return ApiResponse::validationError($errors);
            }

            throw $e;
        });

        $exceptions->renderable(function (ApiException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $e->render();
            }

            throw $e;
        });

        $exceptions->renderable(function (BaseNotFoundHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                throw new NotFoundHttpException();
            }

            throw $e;
        });

        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                throw new UnauthorizedException();
            }

            throw $e;
        });

        $exceptions->renderable(function (ThrottleRequestsException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                throw new TooManyRequestsHttpException();
            }

            throw $e;
        });
    })->create();
