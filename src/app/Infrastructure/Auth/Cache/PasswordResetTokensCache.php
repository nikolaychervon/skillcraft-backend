<?php

namespace App\Infrastructure\Auth\Cache;

use App\Domain\Auth\Cache\PasswordResetTokensCacheInterface;
use Illuminate\Support\Facades\Cache;

class PasswordResetTokensCache implements PasswordResetTokensCacheInterface
{
    private const string PREFIX = 'password_reset_';
    public const int TTL = 60;
    private const string TOKEN_FIELD = 'token';

    /**
     * @param string $email
     * @param string $token
     * @return void
     */
    public function store(string $email, string $token): void
    {
        Cache::put(
            $this->getCacheKey($email),
            [self::TOKEN_FIELD => $token],
            now()->addMinutes(self::TTL)
        );
    }

    /**
     * @param string $email
     * @return string|null
     */
    public function get(string $email): ?string
    {
        $record = Cache::get($this->getCacheKey($email));
        if (!$record || !isset($record[self::TOKEN_FIELD])) return null;
        return $record[self::TOKEN_FIELD];
    }

    /**
     * @param string $email
     * @return void
     */
    public function delete(string $email): void
    {
        Cache::forget($this->getCacheKey($email));
    }

    /**
     * @param string $email
     * @return string
     */
    private function getCacheKey(string $email): string
    {
        return self::PREFIX . md5($email);
    }
}
