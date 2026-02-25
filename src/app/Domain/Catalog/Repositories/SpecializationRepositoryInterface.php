<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Repositories;

use App\Models\ProgrammingLanguage;
use App\Models\Specialization;
use Illuminate\Support\Collection;

interface SpecializationRepositoryInterface
{
    /** @return Collection<int, Specialization> */
    public function getAll(): Collection;

    public function findById(int $id): ?Specialization;

    /** @return Collection<int, ProgrammingLanguage> */
    public function getLanguagesBySpecializationId(int $specializationId): Collection;
}
