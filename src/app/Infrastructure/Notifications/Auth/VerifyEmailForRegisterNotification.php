<?php

declare(strict_types=1);

namespace App\Infrastructure\Notifications\Auth;

use App\Domain\User\Auth\Constants\AuthConstants;
use App\Infrastructure\Notifications\Base\EmailNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class VerifyEmailForRegisterNotification extends EmailNotification
{
    public function __construct(
        private readonly int $userId,
        private readonly string $email,
        private readonly string $firstName,
    ) {}

    public function toMail(mixed $notifiable): MailMessage
    {
        $verificationUrl = $this->generateVerificationUrl();

        return $this->buildMailMessage([
            'name' => $this->firstName,
            'verification_url' => $verificationUrl,
        ]);
    }

    private function generateVerificationUrl(): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(AuthConstants::EMAIL_VERIFICATION_TOKEN_TTL),
            [
                'id' => $this->userId,
                'hash' => sha1($this->email),
            ]
        );
    }
}
