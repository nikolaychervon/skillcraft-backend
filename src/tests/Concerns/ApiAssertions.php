<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Testing\TestResponse;

trait ApiAssertions
{
    protected function assertApiSuccess(TestResponse $response, int $status = 200, ?string $message = null): void
    {
        $response->assertStatus($status)->assertJsonPath('success', true);
        if ($message !== null) {
            $response->assertJsonPath('message', $message);
        }
    }

    protected function assertApiError(TestResponse $response, int $status, ?string $message = null): void
    {
        $response->assertStatus($status)->assertJsonPath('success', false);
        if ($message !== null) {
            $response->assertJsonPath('message', $message);
        }
    }

    protected function assertApiForbidden(TestResponse $response, ?string $message = null): void
    {
        $response->assertStatus(403)->assertJsonPath('success', false);
        if ($message !== null) {
            $response->assertJsonPath('message', $message);
        }
    }

    /**
     * @param  array<int|string, string|array<int, string>>  $errors  e.g. ['email' => 'Required'] or ['email', 'password']
     */
    protected function assertApiValidationErrors(TestResponse $response, array $errors): void
    {
        $response->assertStatus(422)->assertJsonPath('success', false)->assertJsonValidationErrors($errors);
    }
}
