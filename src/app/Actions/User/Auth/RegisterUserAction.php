<?php

namespace App\Actions\User\Auth;

use App\Actions\User\CreateNewUserAction;
use App\DTO\User\CreatingUserDTO;
use App\Models\User;
use App\Notifications\User\VerifyEmailForRegisterNotification;
use App\Repositories\User\UserRepository;

class RegisterUserAction
{
    public function __construct(
        private UserRepository $userRepository,
        private CreateNewUserAction $createNewUserAction,
    ) {
    }

    public function run(CreatingUserDTO $creatingUserDTO): User
    {
        $user = $this->userRepository->findByEmail($creatingUserDTO->getEmail());
        if (!$user instanceof User) {
            $user = $this->createNewUserAction->run($creatingUserDTO);
        }

        $user->notify(new VerifyEmailForRegisterNotification());
        return $user;
    }
}
