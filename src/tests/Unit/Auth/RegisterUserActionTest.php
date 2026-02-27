<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Application\User\Auth\CreateNewUser;
use App\Application\User\Auth\RegisterUser;
use App\Domain\User\Auth\RequestData\CreatingUserRequestData;
use App\Domain\User\Auth\Services\HashServiceInterface;
use App\Domain\User\Auth\Services\NotificationServiceInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\User;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class RegisterUserActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_creates_user_when_not_exists_and_sends_verification_email(): void
    {
        $requestData = new CreatingUserRequestData(
            firstName: 'Иван',
            lastName: 'Петров',
            email: 'ivan@example.com',
            uniqueNickname: 'ivan_petrov',
            password: 'Password123!',
            middleName: null,
        );

        $repo = Mockery::mock(UserRepositoryInterface::class);
        $hashService = app(HashServiceInterface::class);
        $notificationService = Mockery::mock(NotificationServiceInterface::class);

        $user = new User(
            id: 123,
            email: $requestData->email,
            password: 'hash',
            firstName: 'Иван',
            lastName: 'Петров',
            uniqueNickname: 'ivan_petrov',
        );

        $repo->shouldReceive('findByEmail')
            ->once()
            ->with($requestData->email)
            ->andReturn(null);

        $repo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (array $data): bool => $data['email'] === $requestData->email))
            ->andReturn($user);

        $notificationService->shouldReceive('sendEmailVerificationNotification')
            ->once()
            ->with($user)
            ->andReturnNull();

        $createNewUser = new CreateNewUser($repo, $hashService);
        $action = new RegisterUser($repo, $createNewUser, $notificationService);

        $result = $action->run($requestData);

        $this->assertSame($user, $result);
    }

    public function test_it_does_not_create_user_when_exists_but_still_sends_verification_email(): void
    {
        $requestData = new CreatingUserRequestData(
            firstName: 'Иван',
            lastName: 'Петров',
            email: 'ivan@example.com',
            uniqueNickname: 'ivan_petrov',
            password: 'Password123!',
            middleName: null,
        );

        $repo = Mockery::mock(UserRepositoryInterface::class);
        $hashService = app(HashServiceInterface::class);
        $notificationService = Mockery::mock(NotificationServiceInterface::class);

        $existingUser = new User(
            id: 456,
            email: $requestData->email,
            password: 'hash',
            firstName: 'Иван',
            lastName: 'Петров',
            uniqueNickname: 'ivan_petrov',
        );

        $repo->shouldReceive('findByEmail')
            ->once()
            ->with($requestData->email)
            ->andReturn($existingUser);

        $repo->shouldNotReceive('create');

        $notificationService->shouldReceive('sendEmailVerificationNotification')
            ->once()
            ->with($existingUser)
            ->andReturnNull();

        $createNewUser = new CreateNewUser($repo, $hashService);
        $action = new RegisterUser($repo, $createNewUser, $notificationService);

        $result = $action->run($requestData);

        $this->assertSame($existingUser, $result);
    }
}
