<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Repositories\Cached;

use App\Domain\Catalog\Cache\CatalogCacheInterface;
use App\Domain\Catalog\ProgrammingLanguage;
use App\Domain\Catalog\Repositories\ProgrammingLanguageRepositoryInterface;
use Illuminate\Support\Collection;

final class CachedProgrammingLanguageRepository implements ProgrammingLanguageRepositoryInterface
{
    public function __construct(
        private readonly ProgrammingLanguageRepositoryInterface $programmingLanguageRepository,
        private readonly CatalogCacheInterface $cache,
    ) {}

    /** @return Collection<int, ProgrammingLanguage> */
    public function getBySpecializationId(int $specializationId): Collection
    {
        $cached = $this->cache->getSpecializationLanguages($specializationId);
        if ($cached !== null) {
            return $cached;
        }

        $data = $this->programmingLanguageRepository->getBySpecializationId($specializationId);
        $this->cache->putSpecializationLanguages($specializationId, $data);

        return $data;
    }
}
