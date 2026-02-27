<?php

declare(strict_types=1);

namespace App\Domain\User\Auth\Repositories;

interface AuthTokenRepositoryInterface
{
    /** Создаёт токен аутентификации для пользователя по id, возвращает plain-text токен. */
    public function createToken(int $userId, string $tokenName): string;

    /** Удаляет токен текущего запроса для пользователя с указанным id. */
    public function deleteCurrentRequestToken(int $userId): void;

    /** Удаляет все токены пользователя. */
    public function deleteAllTokens(int $userId): void;
}
