<?php

namespace Tests\Unit\Auth;

use App\Application\User\Auth\LogoutAllUser;
use App\Domain\User\Auth\Services\TokenServiceInterface;
use App\Domain\User\User;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class LogoutAllUserActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_deletes_all_tokens(): void
    {
        $tokenService = Mockery::mock(TokenServiceInterface::class);
        $action = new LogoutAllUser($tokenService);

        $user = new User(
            id: 1,
            email: 'u@u.com',
            password: 'hash',
            firstName: 'F',
            lastName: 'L',
            uniqueNickname: 'nick',
        );

        $tokenService->shouldReceive('deleteAllTokens')
            ->once()
            ->with($user)
            ->andReturnNull();

        $action->run($user);
    }
}
