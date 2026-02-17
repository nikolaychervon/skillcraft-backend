<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTO\LoginUserDTO;
use App\Domain\Auth\Exceptions\IncorrectLoginDataException;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\Specifications\UserNotConfirmedSpecification;
use Illuminate\Support\Facades\Hash;

class LoginUserAction
{
    /**
     * @param UserRepositoryInterface $userRepository
     * @param UserNotConfirmedSpecification $userNotConfirmedSpecification
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserNotConfirmedSpecification $userNotConfirmedSpecification
    ) {
    }

    /**
     * @param LoginUserDTO $userDTO
     * @return string
     *
     * @throws IncorrectLoginDataException
     */
    public function run(LoginUserDTO $userDTO): string
    {
        $user = $this->userRepository->findByEmail($userDTO->getEmail());
        if ($this->userNotConfirmedSpecification->isSatisfiedBy($user) || !Hash::check($userDTO->getPassword(), $user->password)) {
            throw new IncorrectLoginDataException();
        }

        return $user->createToken('auth_token')->plainTextToken;
    }
}
