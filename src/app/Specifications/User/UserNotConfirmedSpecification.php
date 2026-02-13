<?php

namespace App\Specifications\User;

use App\Models\User;

class UserNotConfirmedSpecification
{
    public function isSatisfiedBy(?User $user): bool
    {
        return !$user || !$user->hasVerifiedEmail();
    }
}
