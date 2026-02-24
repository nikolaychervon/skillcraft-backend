<?php

declare(strict_types=1);

namespace App\Application\Shared\Constants;

class LevelsConstants
{
    public const string
        UNSETTED = 'NOT_SET',
        JUNIOR_LOW = 'Junior-',
        JUNIOR = 'Junior',
        JUNIOR_HIGH = 'Junior+',
        MIDDLE_LOW = 'Middle-',
        MIDDLE = 'Middle',
        MIDDLE_HIGH = 'Middle+',
        SENIOR_LOW = 'Senior-',
        SENIOR = 'Senior',
        SENIOR_HIGH = 'Senior+';

    public const array LIST = [
        self::UNSETTED,
        self::JUNIOR_LOW,
        self::JUNIOR,
        self::JUNIOR_HIGH,
        self::MIDDLE_LOW,
        self::MIDDLE,
        self::MIDDLE_HIGH,
        self::SENIOR_LOW,
        self::SENIOR,
        self::SENIOR_HIGH,
    ];
}
