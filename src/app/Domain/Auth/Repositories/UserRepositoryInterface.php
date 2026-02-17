<?php

namespace App\Domain\Auth\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Получает пользователя по ID, или отдает null, если пользователь не найден
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User;

    /**
     * Получает пользователя по Email, или отдает null, если пользователь не найден
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;
}
