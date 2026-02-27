<?php

declare(strict_types=1);

namespace App\Domain\User\Auth\Services;

use App\Domain\User\User;

interface TokenServiceInterface
{
    /** Создаёт токен аутентификации для пользователя, возвращает plain-text токен. */
    public function createAuthToken(User $user, string $tokenName): string;

    /** Удаляет текущий токен запроса. */
    public function deleteCurrentToken(User $user): void;

    /** Удаляет все токены пользователя. */
    public function deleteAllTokens(User $user): void;
}
