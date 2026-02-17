<?php

namespace App\Domain\Auth\Actions\Email;

use App\Application\Shared\Exceptions\User\Email\EmailAlreadyVerifiedException;
use App\Application\Shared\Exceptions\User\Email\InvalidConfirmationLinkException;
use App\Application\Shared\Exceptions\User\UserNotFoundException;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Models\User;

class VerifyEmailAction
{
    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    /**
     * @param int $id
     * @param string $hash
     * @return string
     *
     * @throws EmailAlreadyVerifiedException
     * @throws InvalidConfirmationLinkException
     * @throws UserNotFoundException
     */
    public function run(int $id, string $hash): string
    {
        $user = $this->userRepository->findById($id);
        if (!$user instanceof User) {
            throw new UserNotFoundException(['id' => $id]);
        }

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            throw new InvalidConfirmationLinkException();
        }

        if ($user->hasVerifiedEmail()) {
            throw new EmailAlreadyVerifiedException();
        }

        $user->markEmailAsVerified();
        return $user->createToken('auth_token')->plainTextToken;
    }
}
