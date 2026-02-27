<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\User\User;

interface UserRepositoryInterface
{
    /** Поиск пользователя по идентификатору. */
    public function findById(int $id): ?User;

    /** Поиск пользователя по email. */
    public function findByEmail(string $email): ?User;

    /**
     * Создание пользователя. $userData должен содержать уже хешированный пароль (ключ password).
     *
     * @param  array<string, mixed>  $userData
     */
    public function create(array $userData): User;

    /**
     * Обновление атрибутов пользователя.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(User $user, array $attributes): User;

    /** Обновление пароля пользователя. */
    public function updatePassword(User $user, string $hashedPassword): void;

    /** Установка ожидающего подтверждения email. */
    public function setPendingEmail(User $user, string $email): void;

    /** Подтверждение смены email: перенос pending_email в email, сброс pending. */
    public function confirmPendingEmail(User $user): void;

    /** Отметить email пользователя как подтверждённый. */
    public function markEmailVerified(User $user): void;
}
