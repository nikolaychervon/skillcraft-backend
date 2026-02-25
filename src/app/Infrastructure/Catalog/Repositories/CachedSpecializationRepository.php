<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Repositories;

use App\Domain\Catalog\Cache\CatalogCacheInterface;
use App\Domain\Catalog\Repositories\SpecializationRepositoryInterface;
use App\Models\Specialization;
use Illuminate\Support\Collection;

final class CachedSpecializationRepository implements SpecializationRepositoryInterface
{
    public function __construct(
        private readonly SpecializationRepository $specializationRepository,
        private readonly CatalogCacheInterface $cache,
    ) {
    }

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

    public function getLanguagesBySpecializationId(int $specializationId): Collection
    {
        $cached = $this->cache->getSpecializationLanguages($specializationId);
        if ($cached !== null) {
            return $cached;
        }

        $data = $this->specializationRepository->getLanguagesBySpecializationId($specializationId);
        $this->cache->putSpecializationLanguages($specializationId, $data);

        return $data;
    }
}
