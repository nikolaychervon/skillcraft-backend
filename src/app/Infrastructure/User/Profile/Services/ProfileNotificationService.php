<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Profile\Services;

use App\Domain\User\Profile\Services\ProfileNotificationServiceInterface;
use App\Domain\User\User;
use App\Infrastructure\Notifications\Profile\VerifyEmailChangeNotification;
use Illuminate\Support\Facades\Notification;

final class ProfileNotificationService implements ProfileNotificationServiceInterface
{
    public function sendEmailChangeVerificationNotification(User $user, string $pendingEmail): void
    {
        Notification::route('mail', $pendingEmail)->notify(
            new VerifyEmailChangeNotification(
                userId: $user->id,
                name: $user->firstName,
                pendingEmail: $pendingEmail,
            )
        );
    }
}
