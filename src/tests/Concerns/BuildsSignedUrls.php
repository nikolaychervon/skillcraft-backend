<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

trait BuildsSignedUrls
{
    protected function verificationUrl(User $user, int $minutes = 60): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes($minutes),
            ['id' => $user->id, 'hash' => hash('sha256', $user->email)]
        );
    }

    protected function expiredVerificationUrl(User $user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->subMinutes(1),
            ['id' => $user->id, 'hash' => hash('sha256', $user->email)]
        );
    }

    protected function unsignedVerificationUrl(User $user): string
    {
        return '/api/v1/email/verify/'.$user->id.'/'.hash('sha256', $user->email);
    }

    protected function emailChangeVerificationUrl(User $user, string $newEmail, int $minutes = 60): string
    {
        return URL::temporarySignedRoute(
            'profile.email-change.verify',
            Carbon::now()->addMinutes($minutes),
            ['id' => $user->id, 'hash' => hash('sha256', $newEmail)]
        );
    }

    protected function expiredEmailChangeVerificationUrl(User $user, string $newEmail): string
    {
        return URL::temporarySignedRoute(
            'profile.email-change.verify',
            Carbon::now()->subMinutes(1),
            ['id' => $user->id, 'hash' => hash('sha256', $newEmail)]
        );
    }

    protected function unsignedEmailChangeVerificationUrl(int $userId, string $emailHash): string
    {
        return '/api/v1/profile/verify-email-change/'.$userId.'/'.$emailHash;
    }
}
