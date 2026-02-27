<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Repositories\Cached;

use App\Domain\Catalog\Cache\CatalogCacheInterface;
use App\Domain\Catalog\Repositories\SpecializationRepositoryInterface;
use App\Domain\Catalog\Specialization;
use Illuminate\Support\Collection;

final class CachedSpecializationRepository implements SpecializationRepositoryInterface
{
    public function __construct(
        private readonly SpecializationRepositoryInterface $specializationRepository,
        private readonly CatalogCacheInterface $cache,
    ) {}

    /** @return Collection<int, Specialization> */
    public function getAll(): Collection
    {
        $cached = $this->cache->getSpecializations();
        if ($cached !== null) {
            return $cached;
        }

        $data = $this->specializationRepository->getAll();
        $this->cache->putSpecializations($data);

        return $data;
    }

    public function findById(int $id): ?Specialization
    {
        return $this->specializationRepository->findById($id);
    }
}
