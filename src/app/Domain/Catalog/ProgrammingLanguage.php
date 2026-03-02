<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Domain\Shared\DomainModel;

/** Доменная сущность языка программирования. */
final readonly class ProgrammingLanguage extends DomainModel
{
    public function __construct(
        public int $id,
        public string $key,
        public string $name,
    ) {}
}
