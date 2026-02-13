<?php

namespace App\Actions\User\Password;

use App\Cache\User\Auth\PasswordResetTokensCache;
use App\Notifications\User\PasswordResetNotification;
use App\Repositories\User\UserRepository;
use App\Specifications\User\UserNotConfirmedSpecification;
use Illuminate\Support\Str;

class SendPasswordResetLinkAction
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordResetTokensCache $passwordResetTokensCache,
        private UserNotConfirmedSpecification $userNotConfirmedSpecification
    ) {
    }

    public function run(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);
        if ($this->userNotConfirmedSpecification->isSatisfiedBy($user)) {
            return;
        }

        $resetToken = Str::random(64);
        $this->passwordResetTokensCache->save($email, $resetToken);

        $user->notify(new PasswordResetNotification($email, $resetToken));
    }
}
