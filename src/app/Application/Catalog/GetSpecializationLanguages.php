<?php

declare(strict_types=1);

namespace App\Application\Catalog;

use App\Domain\Catalog\ProgrammingLanguage;
use App\Domain\Catalog\Repositories\ProgrammingLanguageRepositoryInterface;
use Illuminate\Support\Collection;

final readonly class GetSpecializationLanguages
{
    public function __construct(
        private ProgrammingLanguageRepositoryInterface $programmingLanguageRepository,
    ) {}

    /** @return Collection<int, ProgrammingLanguage> */
    public function run(int $specializationId): Collection
    {
        return $this->programmingLanguageRepository->getBySpecializationId($specializationId);
    }
}
