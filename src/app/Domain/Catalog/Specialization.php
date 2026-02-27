<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/** Доменная сущность специализации. */
final readonly class Specialization
{
    public function __construct(
        public int $id,
        public string $key,
        public string $name,
    ) {}
}
