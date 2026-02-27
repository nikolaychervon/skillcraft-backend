<?php

declare(strict_types=1);

namespace App\Application\Catalog;

use App\Domain\Catalog\Repositories\SpecializationRepositoryInterface;
use App\Domain\Catalog\Specialization;
use Illuminate\Support\Collection;

final readonly class GetSpecializations
{
    public function __construct(
        private SpecializationRepositoryInterface $specializationRepository,
    ) {}

    /** @return Collection<int, Specialization> */
    public function run(): Collection
    {
        return $this->specializationRepository->getAll();
    }
}
