<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Hydrators;

use App\Domain\Catalog\ProgrammingLanguage;
use Illuminate\Support\Collection;

final class ProgrammingLanguageHydrator
{
    public function toArray(ProgrammingLanguage $language): array
    {
        return [
            'id' => $language->id,
            'key' => $language->key,
            'name' => $language->name,
        ];
    }

    /** @param array{id: int, key: string, name: string} $data */
    public function fromArray(array $data): ProgrammingLanguage
    {
        return new ProgrammingLanguage(
            id: $data['id'],
            key: $data['key'],
            name: $data['name'],
        );
    }

    /**
     * @param  Collection<int, ProgrammingLanguage>  $collection
     * @return array<int, array<string, mixed>>
     */
    public function toArrayCollection(Collection $collection): array
    {
        return $collection->map(fn (ProgrammingLanguage $lang): array => $this->toArray($lang))->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @return Collection<int, ProgrammingLanguage>
     */
    public function fromArrayCollection(array $data): Collection
    {
        return collect($data)->map(fn (array $attrs): ProgrammingLanguage => $this->fromArray($attrs));
    }
}
