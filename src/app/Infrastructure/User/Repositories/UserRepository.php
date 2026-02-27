<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Repositories;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\User;
use App\Infrastructure\User\Mappers\UserMapper;
use App\Models\User as UserModel;
use Illuminate\Support\Facades\DB;

final class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly UserMapper $mapper,
    ) {}

    public function findById(int $id): ?User
    {
        $model = UserModel::query()->find($id);

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $model = UserModel::query()->where('email', $email)->first();

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    /** @param array<string, mixed> $userData */
    public function create(array $userData): User
    {
        $model = UserModel::query()->create($userData);

        return $this->mapper->toDomain($model);
    }

    /** @param array<string, mixed> $attributes */
    public function update(User $user, array $attributes): User
    {
        $this->updateById($user->id, $attributes);
        $model = UserModel::query()->findOrFail($user->id);

        return $this->mapper->toDomain($model);
    }

    public function updatePassword(User $user, string $hashedPassword): void
    {
        $this->updateById($user->id, ['password' => $hashedPassword]);
    }

    public function setPendingEmail(User $user, string $email): void
    {
        $this->updateById($user->id, ['pending_email' => $email]);
    }

    public function confirmPendingEmail(User $user): void
    {
        UserModel::query()->where('id', $user->id)->update([
            'email' => DB::raw('pending_email'),
            'pending_email' => null,
            'email_verified_at' => now(),
        ]);
    }

    public function markEmailVerified(User $user): void
    {
        $this->updateById($user->id, ['email_verified_at' => now()]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function updateById(int $id, array $data): void
    {
        UserModel::query()->where('id', $id)->update($data);
    }
}
