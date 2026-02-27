<?php

declare(strict_types=1);

namespace App\Application\User\Profile;

use App\Domain\User\User;

final readonly class GetUserProfile
{
    public function run(User $user): User
    {
        return $user;
    }
}
