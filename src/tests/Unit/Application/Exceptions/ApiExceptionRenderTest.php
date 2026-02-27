<?php

namespace Tests\Unit\Application\Exceptions;

use App\Application\Shared\Exceptions\Http\UnauthorizedException;
use App\Http\ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
}
