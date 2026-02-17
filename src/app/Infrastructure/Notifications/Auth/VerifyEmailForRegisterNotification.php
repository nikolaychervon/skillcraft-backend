<?php

namespace App\Infrastructure\Notifications\Auth;

use App\Infrastructure\Notifications\Base\EmailNotification;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class VerifyEmailForRegisterNotification extends EmailNotification
{
    private const int VERIFICATION_TOKEN_TTL = 60;

    public function toMail(User $notifiable): MailMessage
    {
        $verificationUrl = $this->generateVerificationUrl($notifiable);

        return $this->buildMailMessage([
            'name' => $notifiable->first_name,
            'verification_url' => $verificationUrl,
        ]);
    }

    protected function generateVerificationUrl(User $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(self::VERIFICATION_TOKEN_TTL),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
