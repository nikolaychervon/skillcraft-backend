<?php

namespace App\Actions\User\Auth;

use App\Exceptions\User\Auth\IncorrectLoginDataException;
use App\Repositories\User\UserRepository;
use App\Specifications\User\UserNotConfirmedSpecification;
use Illuminate\Support\Facades\Hash;

class AuthorizeUserAction
{
    public function __construct(
        private UserRepository $userRepository,
        private UserNotConfirmedSpecification $userNotConfirmedSpecification
    ) {
    }

    /**
     * @return string auth-token
     * @throws IncorrectLoginDataException
     */
    public function run(string $email, string $password): string
    {
        $user = $this->userRepository->findByEmail($email);
        if ($this->userNotConfirmedSpecification->isSatisfiedBy($user) || !Hash::check($password, $user->password)) {
            throw new IncorrectLoginDataException();
        }

        return $user->createToken('auth_token')->plainTextToken;
    }
}
