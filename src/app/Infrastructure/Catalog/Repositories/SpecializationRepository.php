<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Repositories;

use App\Domain\Catalog\Repositories\SpecializationRepositoryInterface;
use App\Domain\Catalog\Specialization;
use App\Infrastructure\Catalog\Mappers\SpecializationMapper;
use App\Models\Specialization as SpecializationModel;
use Illuminate\Support\Collection;

final class SpecializationRepository implements SpecializationRepositoryInterface
{
    public function __construct(
        private readonly SpecializationMapper $mapper,
    ) {}

    /** @return Collection<int, Specialization> */
    public function getAll(): Collection
    {
        return SpecializationModel::query()
            ->orderBy('name')
            ->get()
            ->map(fn (SpecializationModel $m): Specialization => $this->mapper->toDomain($m));
    }

    public function findById(int $id): ?Specialization
    {
        $model = SpecializationModel::query()->find($id);

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }
}
