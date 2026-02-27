<?php

declare(strict_types=1);

namespace App\Domain\User\Auth\Services;

use App\Domain\User\User;

interface NotificationServiceInterface
{
    public function sendEmailVerificationNotification(User $user): void;

    public function sendPasswordResetNotification(string $email, string $resetToken): void;
}
