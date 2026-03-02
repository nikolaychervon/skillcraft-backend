<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Exceptions;

use App\Application\Shared\Exceptions\Http\NotFoundHttpException;
use App\Application\Shared\Exceptions\Http\TooManyRequestsHttpException;
use App\Application\Shared\Exceptions\Http\UnauthorizedException;
use App\Domain\User\Exceptions\Email\InvalidConfirmationLinkException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Http\ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as SymfonyNotFoundHttpException;
use Tests\TestCase;

class ApiExceptionRenderTest extends TestCase
{
    public function test_it_renders_api_exception_as_json_response(): void
    {
        $exception = new UnauthorizedException('Nope');
        $request = Request::create('/api/v1/test', 'GET');

        $response = ExceptionHandler::handle($exception, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(401, $response->getStatusCode());

        $payload = $response->getData(true);

        $this->assertFalse($payload['success']);
        $this->assertSame('Nope', $payload['message']);
        $this->assertArrayNotHasKey('errors', $payload);
    }

    public function test_it_renders_api_exception_with_data(): void
    {
        $exception = new UserNotFoundException(['email' => 'missing@example.com']);
        $request = Request::create('/api/v1/test', 'GET');

        $response = ExceptionHandler::handle($exception, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(404, $response->getStatusCode());

        $payload = $response->getData(true);
        $this->assertFalse($payload['success']);
        $this->assertArrayHasKey('errors', $payload);
        $this->assertSame(['search_data' => ['email' => 'missing@example.com']], $payload['errors']);
    }

    public function test_it_renders_validation_exception(): void
    {
        $exception = ValidationException::withMessages([
            'email' => ['The email field is required.'],
            'password' => ['The password must be at least 8 characters.'],
        ]);
        $request = Request::create('/api/v1/login', 'POST');

        $response = ExceptionHandler::handle($exception, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(422, $response->getStatusCode());

        $payload = $response->getData(true);
        $this->assertFalse($payload['success']);
        $this->assertArrayHasKey('errors', $payload);
        $this->assertSame('The email field is required.', $payload['errors']['email']);
        $this->assertSame('The password must be at least 8 characters.', $payload['errors']['password']);
    }

    public function test_it_renders_not_found_http_exception(): void
    {
        $exception = new SymfonyNotFoundHttpException();
        $request = Request::create('/api/v1/unknown', 'GET');

        $response = ExceptionHandler::handle($exception, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(404, $response->getStatusCode());

        $payload = $response->getData(true);
        $this->assertFalse($payload['success']);
    }

    public function test_it_renders_authentication_exception(): void
    {
        $exception = new AuthenticationException();
        $request = Request::create('/api/v1/profile', 'GET');

        $response = ExceptionHandler::handle($exception, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_it_renders_throttle_exception(): void
    {
        $exception = new ThrottleRequestsException();
        $request = Request::create('/api/v1/register', 'POST');

        $response = ExceptionHandler::handle($exception, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(429, $response->getStatusCode());

        $payload = $response->getData(true);
        $this->assertFalse($payload['success']);
    }

    public function test_it_returns_null_for_non_api_request(): void
    {
        $exception = new UnauthorizedException();
        $request = Request::create('/web/page', 'GET');
        $request->headers->set('Accept', 'text/html');

        $response = ExceptionHandler::handle($exception, $request);

        $this->assertNull($response);
    }

    public function test_it_renders_invalid_signature_exception_as_403_json(): void
    {
        $exception = new InvalidSignatureException();
        $request = Request::create('/api/v1/email/verify/1/abc', 'GET');

        $response = ExceptionHandler::handle($exception, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(403, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertFalse($payload['success']);
        $this->assertSame(__('exceptions.'.InvalidConfirmationLinkException::class), $payload['message']);
    }

    public function test_it_returns_json_500_for_unknown_exception_on_api_request(): void
    {
        $exception = new \RuntimeException('Something broke');
        $request = Request::create('/api/v1/test', 'GET');

        $response = ExceptionHandler::handle($exception, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(500, $response->getStatusCode());

        $payload = $response->getData(true);
        $this->assertFalse($payload['success']);
        $this->assertArrayHasKey('message', $payload);
    }
}
