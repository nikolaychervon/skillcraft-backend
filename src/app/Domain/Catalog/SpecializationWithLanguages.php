<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Domain\Shared\DomainModel;
use Illuminate\Support\Collection;

/** Доменная структура: специализация с коллекцией языков программирования. */
final readonly class SpecializationWithLanguages extends DomainModel
{
    /**
     * @param  Collection<int, ProgrammingLanguage>  $programmingLanguages
     */
    public function __construct(
        public Specialization $specialization,
        public Collection $programmingLanguages,
    ) {}
}
