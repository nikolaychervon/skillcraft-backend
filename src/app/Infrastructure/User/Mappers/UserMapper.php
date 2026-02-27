<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Mappers;

use App\Domain\User\User;
use App\Models\User as UserModel;

/** Маппинг Eloquent User → доменная сущность. */
final class UserMapper
{
    public function toDomain(UserModel $model): User
    {
        $emailVerifiedAt = $model->email_verified_at;
        $emailVerifiedAtImmutable = $emailVerifiedAt !== null
            ? \DateTimeImmutable::createFromMutable($emailVerifiedAt)
            : null;

        return new User(
            id: $model->id,
            email: $model->email,
            password: $model->password,
            firstName: $model->first_name,
            lastName: $model->last_name,
            uniqueNickname: $model->unique_nickname,
            middleName: $model->middle_name,
            pendingEmail: $model->pending_email,
            emailVerifiedAt: $emailVerifiedAtImmutable,
        );
    }
}
