<?php

declare(strict_types=1);

namespace App\Infrastructure\Notifications\Profile;

use App\Domain\User\Profile\Constants\ProfileConstants;
use App\Infrastructure\Notifications\Base\EmailNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class VerifyEmailChangeNotification extends EmailNotification
{
    public function __construct(
        private readonly int $userId,
        private readonly string $name,
        private readonly string $pendingEmail,
    ) {}

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->generateVerificationUrl();

        return $this->buildMailMessage([
            'name' => $this->name,
            'pending_email' => $this->pendingEmail,
            'verification_url' => $verificationUrl,
        ]);
    }

    private function generateVerificationUrl(): string
    {
        return URL::temporarySignedRoute(
            'profile.email-change.verify',
            now()->addMinutes(ProfileConstants::EMAIL_CHANGE_VERIFICATION_TTL),
            [
                'id' => $this->userId,
                'hash' => sha1($this->pendingEmail),
            ]
        );
    }
}
