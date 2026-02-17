<?php

namespace App\Domain\Auth\Actions\Email;

use App\Application\Shared\Exceptions\User\Email\EmailAlreadyVerifiedException;
use App\Domain\Auth\DTO\ResendEmailDTO;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Infrastructure\Notifications\Auth\VerifyEmailForRegisterNotification;
use App\Models\User;

class ResendEmailAction
{
    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    /**
     * @param ResendEmailDTO $resendEmailDTO
     * @return void
     *
     * @throws EmailAlreadyVerifiedException
     */
    public function run(ResendEmailDTO $resendEmailDTO): void
    {
        $user = $this->userRepository->findByEmail($resendEmailDTO->getEmail());
        if (!$user instanceof User) {
            return;
        }

        if ($user->hasVerifiedEmail()) {
            throw new EmailAlreadyVerifiedException();
        }

        $user->notify(new VerifyEmailForRegisterNotification());
    }
}
