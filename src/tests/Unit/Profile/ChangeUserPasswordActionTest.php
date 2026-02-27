<?php

namespace Tests\Unit\Profile;

use App\Application\User\Profile\ChangeUserPassword;
use App\Domain\User\Auth\Services\HashServiceInterface;
use App\Domain\User\Profile\Exceptions\IncorrectCurrentPasswordException;
use App\Domain\User\Profile\RequestData\ChangeUserPasswordRequestData;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\User;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class ChangeUserPasswordActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_throws_when_old_password_incorrect(): void
    {
        $repo = Mockery::mock(UserRepositoryInterface::class);
        $hash = Mockery::mock(HashServiceInterface::class);
        $action = new ChangeUserPassword($repo, $hash);

        $user = new User(
            id: 1,
            email: 'u@u.com',
            password: 'hashed',
            firstName: 'F',
            lastName: 'L',
            uniqueNickname: 'nick',
        );

        $requestData = new ChangeUserPasswordRequestData('wrong', 'new');

        $hash->shouldReceive('check')->once()->with('wrong', 'hashed')->andReturnFalse();
        $repo->shouldNotReceive('updatePassword');

        $this->expectException(IncorrectCurrentPasswordException::class);
        $action->run($user, $requestData);
    }

    public function test_it_updates_password_when_old_password_correct(): void
    {
        $repo = Mockery::mock(UserRepositoryInterface::class);
        $hash = Mockery::mock(HashServiceInterface::class);
        $action = new ChangeUserPassword($repo, $hash);

        $user = new User(
            id: 1,
            email: 'u@u.com',
            password: 'hashed',
            firstName: 'F',
            lastName: 'L',
            uniqueNickname: 'nick',
        );

        $requestData = new ChangeUserPasswordRequestData('old', 'new');

        $hash->shouldReceive('check')->once()->with('old', 'hashed')->andReturnTrue();
        $hash->shouldReceive('make')->once()->with('new')->andReturn('new_hashed');

        $repo->shouldReceive('updatePassword')->once()->with($user, 'new_hashed')->andReturnNull();

        $action->run($user, $requestData);
    }
}
