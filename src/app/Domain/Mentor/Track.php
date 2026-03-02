<?php

declare(strict_types=1);

namespace App\Domain\Mentor;

use App\Domain\Catalog\ProgrammingLanguage;
use App\Domain\Catalog\Specialization;
use App\Domain\Shared\DomainModel;
use DateTimeImmutable;

final readonly class Track extends DomainModel
{
    public function __construct(
        public int $id,
        public string $key,
        public string $name,
        public DateTimeImmutable $createdAt,
        public Specialization $specialization,
        public ProgrammingLanguage $programmingLanguage,
    ) {}
}
