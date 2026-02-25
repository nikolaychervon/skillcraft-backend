<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Repositories\SpecializationRepositoryInterface;
use App\Models\Specialization;
use Illuminate\Support\Collection;

class GetSpecializationsAction
{
    public function __construct(
        private readonly SpecializationRepositoryInterface $specializationRepository
    ) {
    }

    /** @return Collection<int, Specialization> */
    public function run(): Collection
    {
        return $this->specializationRepository->getAll();
    }
}
