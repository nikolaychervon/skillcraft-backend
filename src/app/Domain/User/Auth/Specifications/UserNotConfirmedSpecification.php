<?php

declare(strict_types=1);

namespace App\Domain\User\Auth\Specifications;

use App\Domain\User\User;

final readonly class UserNotConfirmedSpecification
{
    public function isSatisfiedBy(?User $user): bool
    {
        return $user === null || !$user->hasVerifiedEmail();
    }
}
