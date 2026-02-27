<?php

declare(strict_types=1);

namespace App\Infrastructure\Notifications\Auth;

use App\Domain\User\Auth\Constants\AuthConstants;
use App\Infrastructure\Notifications\Base\EmailNotification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetNotification extends EmailNotification
{
    public function __construct(
        private readonly string $email,
        private readonly string $resetToken,
    ) {}

    public function toMail($notifiable): MailMessage
    {
        return $this->buildMailMessage([
            'reset_url' => $this->generateResetUrl(),
            'expires_minutes' => AuthConstants::PASSWORD_RESET_TOKEN_TTL,
        ]);
    }

    protected function generateResetUrl(): string
    {
        $frontendUrl = config('app.url');

        return "$frontendUrl/reset-password?reset_token=$this->resetToken&email=$this->email";
    }
}
