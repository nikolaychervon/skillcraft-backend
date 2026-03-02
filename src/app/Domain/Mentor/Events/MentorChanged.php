<?php

declare(strict_types=1);

namespace App\Domain\Mentor\Events;

final class MentorChanged
{
    public function __construct(
        public readonly int $userId,
    ) {}
}
