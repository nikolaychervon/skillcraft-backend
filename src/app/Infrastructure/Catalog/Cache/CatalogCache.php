<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Cache;

use App\Domain\Catalog\Cache\CatalogCacheInterface;
use App\Infrastructure\Catalog\Hydrators\ProgrammingLanguageHydrator;
use App\Infrastructure\Catalog\Hydrators\SpecializationHydrator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Кэш каталога в Redis. Ключи и TTL — здесь.
 * В хранилище пишется JSON
 */
final class CatalogCache implements CatalogCacheInterface
{
    private const int TTL_SECONDS = 60;
    private const string KEY_SPECIALIZATIONS = 'catalog.specializations';
    private const string KEY_SPECIALIZATION_LANGUAGES = 'catalog.specializations.%d.languages';

    public function __construct(
        private readonly SpecializationHydrator $specializationHydrator,
        private readonly ProgrammingLanguageHydrator $languageHydrator,
    ) {
    }

    public function getSpecializations(): ?Collection
    {
        $raw = Cache::get(self::KEY_SPECIALIZATIONS);
        if (!is_string($raw)) {
            return null;
        }

        $cached = json_decode($raw, true);
        if (!is_array($cached)) {
            return null;
        }

        return $this->specializationHydrator->fromArrayCollection($cached);
    }

    public function putSpecializations(Collection $specializations): void
    {
        $payload = $this->specializationHydrator->toArrayCollection($specializations);
        Cache::put(self::KEY_SPECIALIZATIONS, json_encode($payload), self::TTL_SECONDS);
    }

    public function deleteSpecializations(): void
    {
        Cache::forget(self::KEY_SPECIALIZATIONS);
    }

    public function getSpecializationLanguages(int $specializationId): ?Collection
    {
        $key = sprintf(self::KEY_SPECIALIZATION_LANGUAGES, $specializationId);
        $raw = Cache::get($key);
        if (!is_string($raw)) {
            return null;
        }

        $cached = json_decode($raw, true);
        if (!is_array($cached)) {
            return null;
        }

        return $this->languageHydrator->fromArrayCollection($cached);
    }

    public function putSpecializationLanguages(int $specializationId, Collection $languages): void
    {
        $key = sprintf(self::KEY_SPECIALIZATION_LANGUAGES, $specializationId);
        $payload = $this->languageHydrator->toArrayCollection($languages);
        Cache::put($key, json_encode($payload), self::TTL_SECONDS);
    }

    public function deleteSpecializationLanguages(int $specializationId): void
    {
        $key = sprintf(self::KEY_SPECIALIZATION_LANGUAGES, $specializationId);
        Cache::forget($key);
    }
}
