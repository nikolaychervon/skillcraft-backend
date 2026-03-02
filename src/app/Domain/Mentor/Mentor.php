<?php

declare(strict_types=1);

namespace App\Domain\Mentor;

use App\Domain\Shared\DomainModel;
use DateTimeImmutable;

final readonly class Mentor extends DomainModel
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $name,
        public string $slug,
        public string $targetLevel,
        public ?string $currentLevel,
        public string $howToCallMe,
        public bool $useNameToCallMe,
        public string $mentorPersona,
        public DateTimeImmutable $createdAt,
        public Track $track,
    ) {}
}
