<?php

declare(strict_types=1);

namespace App\Application\Shared\Constants;

class MentorPersonaConstants
{
    public const string
        STRICT = 'strict',
        FRIENDLY = 'friendly',
        NEUTRAL = 'neutral';

    public const array LIST = [
        self::STRICT,
        self::FRIENDLY,
        self::NEUTRAL,
    ];
}
