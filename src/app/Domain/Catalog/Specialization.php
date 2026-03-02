<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Domain\Shared\DomainModel;

/** Доменная сущность специализации. */
final readonly class Specialization extends DomainModel
{
    public function __construct(
        public int $id,
        public string $key,
        public string $name,
    ) {}
}
