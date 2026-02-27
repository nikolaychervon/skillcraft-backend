<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/** Доменная сущность языка программирования. */
final readonly class ProgrammingLanguage
{
    public function __construct(
        public int $id,
        public string $key,
        public string $name,
    ) {}
}
