<?php

declare(strict_types=1);

namespace App\Domain\User\Profile\Services;

use App\Domain\User\User;

interface ProfileNotificationServiceInterface
{
    /** Отправляет письмо со ссылкой подтверждения смены email на адрес $pendingEmail. */
    public function sendEmailChangeVerificationNotification(User $user, string $pendingEmail): void;
}
