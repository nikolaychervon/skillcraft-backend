<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Cache;

use App\Domain\Catalog\ProgrammingLanguage;
use App\Domain\Catalog\Specialization;
use Illuminate\Support\Collection;

interface CatalogCacheInterface
{
    /** @return Collection<int, Specialization>|null */
    public function getSpecializations(): ?Collection;

    /** @param Collection<int, Specialization> $specializations */
    public function putSpecializations(Collection $specializations): void;

    public function deleteSpecializations(): void;

    /** @return Collection<int, ProgrammingLanguage>|null */
    public function getSpecializationLanguages(int $specializationId): ?Collection;

    /** @param Collection<int, ProgrammingLanguage> $languages */
    public function putSpecializationLanguages(int $specializationId, Collection $languages): void;

    public function deleteSpecializationLanguages(int $specializationId): void;
}
