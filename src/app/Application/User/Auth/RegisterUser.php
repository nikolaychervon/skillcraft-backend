<?php

declare(strict_types=1);

namespace App\Application\User\Auth;

use App\Domain\User\Auth\RequestData\CreatingUserRequestData;
use App\Domain\User\Auth\Services\NotificationServiceInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\User;

final readonly class RegisterUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private CreateNewUser $createNewUser,
        private NotificationServiceInterface $notificationService,
    ) {}

    public function run(CreatingUserRequestData $data): User
    {
        $user = $this->userRepository->findByEmail($data->email);

        if ($user === null) {
            $user = $this->createNewUser->run($data);
        }

        $this->notificationService->sendEmailVerificationNotification($user);

        return $user;
    }
}
