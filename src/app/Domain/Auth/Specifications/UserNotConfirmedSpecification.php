<?php

namespace App\Domain\Auth\Specifications;

use App\Models\User;

/**
 * Проверка на неподтвержденного пользователя
 */
class UserNotConfirmedSpecification
{
    /**
     * @param User|null $user
     * @return bool
     */
    public function isSatisfiedBy(?User $user): bool
    {
        return !$user || !$user->hasVerifiedEmail();
    }
}
