<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth\Cache;

use App\Domain\User\Auth\Cache\PasswordResetTokensCacheInterface;
use App\Domain\User\Auth\Constants\AuthConstants;
use Illuminate\Support\Facades\Cache;

class PasswordResetTokensCache implements PasswordResetTokensCacheInterface
{
    private const string PREFIX = 'password_reset_';

    private const string TOKEN_FIELD = 'token';

    public function store(string $email, string $token): void
    {
        Cache::put(
            $this->getCacheKey($email),
            [self::TOKEN_FIELD => $token],
            now()->addMinutes(AuthConstants::PASSWORD_RESET_TOKEN_TTL)
        );
    }

    public function get(string $email): ?string
    {
        $record = Cache::get($this->getCacheKey($email));
        if (!$record || !isset($record[self::TOKEN_FIELD])) {
            return null;
        }

        return $record[self::TOKEN_FIELD];
    }

    public function delete(string $email): void
    {
        Cache::forget($this->getCacheKey($email));
    }

    private function getCacheKey(string $email): string
    {
        return self::PREFIX.hash('sha256', $email);
    }
}
