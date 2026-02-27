<?php

declare(strict_types=1);

namespace App\Application\User\Auth;

use App\Domain\User\Auth\Cache\PasswordResetTokensCacheInterface;
use App\Domain\User\Auth\Services\NotificationServiceInterface;
use App\Domain\User\Auth\Services\ResetTokenGeneratorInterface;
use App\Domain\User\Auth\Specifications\UserNotConfirmedSpecification;
use App\Domain\User\Repositories\UserRepositoryInterface;

final readonly class SendPasswordResetLink
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordResetTokensCacheInterface $passwordResetTokensCache,
        private NotificationServiceInterface $notificationService,
        private ResetTokenGeneratorInterface $tokenGenerator,
        private UserNotConfirmedSpecification $userNotConfirmedSpecification,
    ) {}

    public function run(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);
        if ($this->userNotConfirmedSpecification->isSatisfiedBy($user)) {
            return;
        }

        $resetToken = $this->tokenGenerator->generate(64);
        $this->passwordResetTokensCache->store($email, $resetToken);
        $this->notificationService->sendPasswordResetNotification($email, $resetToken);
    }
}
