<?php

namespace App\Domain\Auth\Actions\Password;

use App\Domain\Auth\Cache\PasswordResetTokensCacheInterface;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\Specifications\UserNotConfirmedSpecification;
use App\Infrastructure\Notifications\Auth\PasswordResetNotification;
use Illuminate\Support\Str;

class SendPasswordResetLinkAction
{
    /**
     * @param UserRepositoryInterface $userRepository
     * @param PasswordResetTokensCacheInterface $passwordResetTokensCache
     * @param UserNotConfirmedSpecification $userNotConfirmedSpecification
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordResetTokensCacheInterface $passwordResetTokensCache,
        private UserNotConfirmedSpecification $userNotConfirmedSpecification
    ) {
    }

    /**
     * @param string $email
     * @return void
     */
    public function run(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);
        if ($this->userNotConfirmedSpecification->isSatisfiedBy($user)) {
            return;
        }

        $resetToken = Str::random(64);
        $this->passwordResetTokensCache->store($email, $resetToken);

        $user->notify(new PasswordResetNotification($email, $resetToken));
    }
}
