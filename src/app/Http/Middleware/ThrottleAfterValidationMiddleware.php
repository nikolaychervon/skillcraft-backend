<?php

namespace App\Http\Middleware;

use App\Application\Shared\Exceptions\Http\TooManyRequestsHttpException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ThrottleAfterValidationMiddleware
{
    private const string THROTTLE_KEY = 'throttle_';

    /**
     * @throws TooManyRequestsHttpException
     */
    public function handle(Request $request, Closure $next, int $maxAttempts, int $decayMinutes, string $keyField = 'email')
    {
        $key = $this->getKey($request, $keyField);
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw new TooManyRequestsHttpException();
        }

        $response = $next($request);

        if ($response->getStatusCode() < 400) {
            RateLimiter::hit($key, $decayMinutes * 60);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param string $keyField
     * @return string
     */
    private function getKey(Request $request, string $keyField): string
    {
        return self::THROTTLE_KEY . $request->getRequestUri() . '_' . $request->input($keyField) . '_' . $request->ip();
    }
}
