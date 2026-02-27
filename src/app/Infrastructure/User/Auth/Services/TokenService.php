<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth\Services;

use App\Domain\User\Auth\Repositories\AuthTokenRepositoryInterface;
use App\Domain\User\Auth\Services\TokenServiceInterface;
use App\Domain\User\User;

final class TokenService implements TokenServiceInterface
{
    public function __construct(
        private readonly AuthTokenRepositoryInterface $authTokenRepository,
    ) {}

    public function createAuthToken(User $user, string $tokenName): string
    {
        return $this->authTokenRepository->createToken($user->id, $tokenName);
    }

    public function deleteCurrentToken(User $user): void
    {
        $this->authTokenRepository->deleteCurrentRequestToken($user->id);
    }

    public function deleteAllTokens(User $user): void
    {
        $this->authTokenRepository->deleteAllTokens($user->id);
    }
}
