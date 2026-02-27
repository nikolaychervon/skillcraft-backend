<?php

declare(strict_types=1);

namespace App\Domain\User\Auth\Constants;

class AuthConstants
{
    public const string DEFAULT_TOKEN_NAME = 'auth_token';

    public const int PASSWORD_RESET_TOKEN_TTL = 60;

    public const int EMAIL_VERIFICATION_TOKEN_TTL = 60;
}
