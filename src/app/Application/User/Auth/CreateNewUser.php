<?php

declare(strict_types=1);

namespace App\Application\User\Auth;

use App\Domain\User\Auth\RequestData\CreatingUserRequestData;
use App\Domain\User\Auth\Services\HashServiceInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\User;

final readonly class CreateNewUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private HashServiceInterface $hashService,
    ) {}

    public function run(CreatingUserRequestData $data): User
    {
        $hashedPassword = $this->hashService->make($data->password);

        return $this->userRepository->create([
            'first_name' => $data->firstName,
            'last_name' => $data->lastName,
            'middle_name' => $data->middleName,
            'email' => $data->email,
            'password' => $hashedPassword,
            'unique_nickname' => $data->uniqueNickname,
        ]);
    }
}
