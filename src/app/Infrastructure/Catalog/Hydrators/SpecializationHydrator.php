<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Hydrators;

use App\Models\Specialization;
use Illuminate\Support\Collection;

/** Specialization ↔ array для сериализации (кэш, экспорт). */
final class SpecializationHydrator
{
    public function toArray(Specialization $model): array
    {
        return $model->toArray();
    }

    public function fromArray(array $data): Specialization
    {
        $model = new Specialization();
        $model->setRawAttributes($data);
        return $model;
    }

    /**
     * @param Collection<int, Specialization> $collection
     * @return array<int, array<string, mixed>>
     */
    public function toArrayCollection(Collection $collection): array
    {
        return $collection->map(fn (Specialization $s): array => $this->toArray($s))->all();
    }

    /**
     * @param array<int, array<string, mixed>> $data
     * @return Collection<int, Specialization>
     */
    public function fromArrayCollection(array $data): Collection
    {
        return collect($data)->map(fn (array $attrs): Specialization => $this->fromArray($attrs));
    }
}
