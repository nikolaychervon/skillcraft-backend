<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Hydrators;

use App\Domain\Catalog\Specialization;
use Illuminate\Support\Collection;

final class SpecializationHydrator
{
    public function toArray(Specialization $specialization): array
    {
        return [
            'id' => $specialization->id,
            'key' => $specialization->key,
            'name' => $specialization->name,
        ];
    }

    /** @param array{id: int, key: string, name: string} $data */
    public function fromArray(array $data): Specialization
    {
        return new Specialization(
            id: $data['id'],
            key: $data['key'],
            name: $data['name'],
        );
    }

    /**
     * @param  Collection<int, Specialization>  $collection
     * @return array<int, array<string, mixed>>
     */
    public function toArrayCollection(Collection $collection): array
    {
        return $collection->map(fn (Specialization $s): array => $this->toArray($s))->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @return Collection<int, Specialization>
     */
    public function fromArrayCollection(array $data): Collection
    {
        return collect($data)->map(fn (array $attrs): Specialization => $this->fromArray($attrs));
    }
}
