<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Repositories;

use App\Domain\Catalog\ProgrammingLanguage;
use App\Domain\Catalog\Repositories\ProgrammingLanguageRepositoryInterface;
use App\Infrastructure\Catalog\Mappers\ProgrammingLanguageMapper;
use App\Models\ProgrammingLanguage as ProgrammingLanguageModel;
use App\Models\Specialization;
use Illuminate\Support\Collection;

final class ProgrammingLanguageRepository implements ProgrammingLanguageRepositoryInterface
{
    public function __construct(
        private readonly ProgrammingLanguageMapper $mapper,
    ) {}

    /** @return Collection<int, ProgrammingLanguage> */
    public function getBySpecializationId(int $specializationId): Collection
    {
        $specialization = Specialization::query()
            ->with('programmingLanguages')
            ->find($specializationId);

        if ($specialization === null) {
            return collect();
        }

        return $specialization->programmingLanguages
            ->map(fn (ProgrammingLanguageModel $m): ProgrammingLanguage => $this->mapper->toDomain($m));
    }
}
