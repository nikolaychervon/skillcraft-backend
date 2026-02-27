<?php

declare(strict_types=1);

namespace App\Application\User\Profile;

use App\Domain\User\Auth\Services\HashServiceInterface;
use App\Domain\User\Profile\Exceptions\IncorrectCurrentPasswordException;
use App\Domain\User\Profile\RequestData\ChangeUserPasswordRequestData;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\User;

final readonly class ChangeUserPassword
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private HashServiceInterface $hashService,
    ) {}

    /** @throws IncorrectCurrentPasswordException */
    public function run(User $user, ChangeUserPasswordRequestData $data): void
    {
        if (!$this->hashService->check($data->oldPassword, $user->password)) {
            throw new IncorrectCurrentPasswordException;
        }

        $hashedPassword = $this->hashService->make($data->password);
        $this->userRepository->updatePassword($user, $hashedPassword);
    }
}
