<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth\Services;

use App\Domain\User\Auth\Services\NotificationServiceInterface;
use App\Domain\User\User;
use App\Infrastructure\Notifications\Auth\PasswordResetNotification;
use App\Infrastructure\Notifications\Auth\VerifyEmailForRegisterNotification;
use Illuminate\Support\Facades\Notification;

final class NotificationService implements NotificationServiceInterface
{
    public function sendEmailVerificationNotification(User $user): void
    {
        Notification::route('mail', $user->email)->notify(
            new VerifyEmailForRegisterNotification(
                userId: $user->id,
                email: $user->email,
                firstName: $user->firstName,
            )
        );
    }

    public function sendPasswordResetNotification(string $email, string $resetToken): void
    {
        Notification::route('mail', $email)->notify(
            new PasswordResetNotification($email, $resetToken)
        );
    }
}
