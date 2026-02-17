<?php

namespace App\Infrastructure\Auth\Repositories;

use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public const string MODEL = User::class;

    /**
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return self::MODEL::query()->find($id);
    }

    /**
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return self::MODEL::query()->where('email', $email)->first();
    }
}
