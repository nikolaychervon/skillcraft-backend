<?php

namespace Tests\Unit\Auth;

use App\Application\User\Auth\LogoutUser;
use App\Domain\User\Auth\Services\TokenServiceInterface;
use App\Domain\User\User;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class LogoutUserActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_deletes_current_token(): void
    {
        $tokenService = Mockery::mock(TokenServiceInterface::class);
        $action = new LogoutUser($tokenService);

        $user = new User(
            id: 1,
            email: 'u@u.com',
            password: 'hash',
            firstName: 'F',
            lastName: 'L',
            uniqueNickname: 'nick',
        );

        $tokenService->shouldReceive('deleteCurrentToken')
            ->once()
            ->with($user)
            ->andReturnNull();

        $action->run($user);
    }
}
