<?php

declare(strict_types=1);

namespace App\Application\User\Profile;

use App\Domain\User\Profile\RequestData\UpdateUserProfileRequestData;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\User;

final readonly class UpdateUserProfile
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function run(User $user, UpdateUserProfileRequestData $data): User
    {
        return $this->userRepository->update($user, [
            'first_name' => $data->firstName,
            'last_name' => $data->lastName,
            'middle_name' => $data->middleName,
            'unique_nickname' => $data->uniqueNickname,
        ]);
    }
}
