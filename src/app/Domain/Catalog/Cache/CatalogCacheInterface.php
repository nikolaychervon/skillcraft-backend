<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Cache;

use App\Models\ProgrammingLanguage;
use App\Models\Specialization;
use Illuminate\Support\Collection;

/**
 * Кэш каталога. Ключи и TTL — в реализации (инфраструктура).
 */
interface CatalogCacheInterface
{
    /** @return Collection<int, Specialization>|null */
    public function getSpecializations(): ?Collection;

    public function putSpecializations(Collection $specializations): void;

    public function deleteSpecializations(): void;

    /** @return Collection<int, ProgrammingLanguage>|null */
    public function getSpecializationLanguages(int $specializationId): ?Collection;

    /** @param Collection<int, ProgrammingLanguage> $languages */
    public function putSpecializationLanguages(int $specializationId, Collection $languages): void;

    public function deleteSpecializationLanguages(int $specializationId): void;
}
