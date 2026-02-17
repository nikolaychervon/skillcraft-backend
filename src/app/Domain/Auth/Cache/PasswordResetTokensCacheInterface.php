<?php

namespace App\Domain\Auth\Cache;

interface PasswordResetTokensCacheInterface
{
    /**
     * Сохраняет токен для конкретного email
     *
     * @param string $email
     * @param string $token
     * @return void
     */
    public function store(string $email, string $token): void;

    /**
     * Получает token пользователя по email или отдает null, если кэш просрочен
     *
     * @param string $email
     * @return string|null
     */
    public function get(string $email): ?string;

    /**
     * Удаляет токен по email
     *
     * @param string $email
     * @return void
     */
    public function delete(string $email): void;
}
