<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\ProgrammingLanguage;
use Illuminate\Support\Collection;

interface ProgrammingLanguageRepositoryInterface
{
    /**
     * Вернуть языки программирования, доступные в заданной специализации.
     *
     * @return Collection<int, ProgrammingLanguage>
     */
    public function getBySpecializationId(int $specializationId): Collection;
}
