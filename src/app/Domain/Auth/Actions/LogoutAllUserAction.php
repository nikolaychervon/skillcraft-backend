<?php

namespace App\Domain\Auth\Actions;

use App\Models\User;

class LogoutAllUserAction
{
    /**
     * @param User $user
     * @return void
     */
    public function run(User $user): void
    {
        $user->tokens()->delete();
    }
}
