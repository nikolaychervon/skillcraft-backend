<?php

declare(strict_types=1);

namespace App\Application\User\Auth;

use App\Domain\User\Auth\Constants\AuthConstants;
use App\Domain\User\Auth\Exceptions\IncorrectLoginDataException;
use App\Domain\User\Auth\RequestData\LoginUserRequestData;
use App\Domain\User\Auth\Services\HashServiceInterface;
use App\Domain\User\Auth\Services\TokenServiceInterface;
use App\Domain\User\Auth\Specifications\UserNotConfirmedSpecification;
use App\Domain\User\Repositories\UserRepositoryInterface;

final readonly class LoginUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private HashServiceInterface $hashService,
        private TokenServiceInterface $tokenService,
        private UserNotConfirmedSpecification $userNotConfirmedSpecification,
    ) {}

    /** @throws IncorrectLoginDataException */
    public function run(LoginUserRequestData $data): string
    {
        $user = $this->userRepository->findByEmail($data->email);

        if ($this->userNotConfirmedSpecification->isSatisfiedBy($user)) {
            throw new IncorrectLoginDataException;
        }

        if (!$this->hashService->check($data->password, $user->password)) {
            throw new IncorrectLoginDataException;
        }

        return $this->tokenService->createAuthToken($user, AuthConstants::DEFAULT_TOKEN_NAME);
    }
}
