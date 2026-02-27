<?php

declare(strict_types=1);

namespace App\Application\User\Auth;

use App\Domain\User\Auth\Services\TokenServiceInterface;
use App\Domain\User\User;

final readonly class LogoutUser
{
    public function __construct(
        private TokenServiceInterface $tokenService,
    ) {}

    public function run(User $user): void
    {
        $this->tokenService->deleteCurrentToken($user);
    }
}
