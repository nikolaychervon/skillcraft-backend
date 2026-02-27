<?php

namespace Tests\Unit\Profile;

use App\Application\User\Profile\ChangeUserEmail;
use App\Domain\User\Profile\RequestData\ChangeUserEmailRequestData;
use App\Domain\User\Profile\Services\ProfileNotificationServiceInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\User;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class ChangeUserEmailActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_sets_pending_email_and_sends_verification_notification(): void
    {
        $repo = Mockery::mock(UserRepositoryInterface::class);
        $notificationService = Mockery::mock(ProfileNotificationServiceInterface::class);

        $action = new ChangeUserEmail($repo, $notificationService);

        $user = new User(
            id: 1,
            email: 'old@example.com',
            password: 'hash',
            firstName: 'F',
            lastName: 'L',
            uniqueNickname: 'nick',
        );
        $requestData = new ChangeUserEmailRequestData('new@example.com');

        $repo->shouldReceive('setPendingEmail')
            ->once()
            ->with($user, 'new@example.com')
            ->andReturnNull();

        $notificationService->shouldReceive('sendEmailChangeVerificationNotification')
            ->once()
            ->with($user, 'new@example.com')
            ->andReturnNull();

        $action->run($user, $requestData);
    }
}
