<?php

namespace Tests\Unit\Http\Middleware;

use App\Application\Shared\Exceptions\Http\TooManyRequestsHttpException;
use App\Http\Middleware\ThrottleAfterValidationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ThrottleAfterValidationMiddlewareTest extends TestCase
{
    public function test_it_throws_when_too_many_attempts(): void
    {
        $middleware = new ThrottleAfterValidationMiddleware;

        $request = Request::create('/api/auth/login', 'POST', ['email' => 'a@b.com']);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->andReturnTrue();

        RateLimiter::shouldReceive('hit')->never();

        $this->expectException(TooManyRequestsHttpException::class);

        $middleware->handle($request, function () {
            $this->fail('Next middleware should not be called when throttled.');
        }, 5, 1, 'email');
    }

    public function test_it_hits_rate_limiter_only_on_success_response(): void
    {
        $middleware = new ThrottleAfterValidationMiddleware;

        $request = Request::create('/api/auth/login', 'POST', ['email' => 'a@b.com']);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->andReturnFalse();

        RateLimiter::shouldReceive('hit')
            ->once()
            ->andReturnNull();

        $response = $middleware->handle($request, fn () => response()->json([], 200), 5, 1, 'email');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_it_does_not_hit_rate_limiter_on_error_response(): void
    {
        $middleware = new ThrottleAfterValidationMiddleware;

        $request = Request::create('/api/auth/login', 'POST', ['email' => 'a@b.com']);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->andReturnFalse();

        RateLimiter::shouldReceive('hit')->never();

        $response = $middleware->handle($request, fn () => response()->json([], 422), 5, 1, 'email');

        $this->assertSame(422, $response->getStatusCode());
    }
}
