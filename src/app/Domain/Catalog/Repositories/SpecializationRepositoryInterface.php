<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Specialization;
use Illuminate\Support\Collection;

interface SpecializationRepositoryInterface
{
    /** @return Collection<int, Specialization> */
    public function getAll(): Collection;

    public function findById(int $id): ?Specialization;
}
