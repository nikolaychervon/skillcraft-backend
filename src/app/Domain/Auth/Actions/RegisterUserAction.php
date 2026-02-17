<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTO\CreatingUserDTO;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Infrastructure\Notifications\Auth\VerifyEmailForRegisterNotification;
use App\Models\User;

class RegisterUserAction
{
    /**
     * @param UserRepositoryInterface $userRepository
     * @param CreateNewUserAction $createNewUserAction
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private CreateNewUserAction $createNewUserAction,
    ) {
    }

    /**
     * @param CreatingUserDTO $creatingUserDTO
     * @return User
     */
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
