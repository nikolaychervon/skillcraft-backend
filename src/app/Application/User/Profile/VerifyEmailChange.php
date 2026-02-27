<?php

declare(strict_types=1);

namespace App\Application\User\Profile;

use App\Domain\User\Exceptions\Email\InvalidConfirmationLinkException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\Repositories\UserRepositoryInterface;

final readonly class VerifyEmailChange
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    /** @throws InvalidConfirmationLinkException|UserNotFoundException */
    public function run(int $id, string $hash): void
    {
        $user = $this->userRepository->findById($id);
        if ($user === null) {
            throw new UserNotFoundException(['id' => $id]);
        }

        if ($user->pendingEmail === null) {
            throw new InvalidConfirmationLinkException;
        }

        if (!hash_equals($hash, sha1($user->pendingEmail))) {
            throw new InvalidConfirmationLinkException;
        }

        $this->userRepository->confirmPendingEmail($user);
    }
}
