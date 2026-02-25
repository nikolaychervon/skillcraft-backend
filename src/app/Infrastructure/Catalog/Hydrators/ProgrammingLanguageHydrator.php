<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Hydrators;

use App\Models\ProgrammingLanguage;
use Illuminate\Support\Collection;

final class ProgrammingLanguageHydrator
{
    public function toArray(ProgrammingLanguage $model): array
    {
        return $model->toArray();
    }

    public function fromArray(array $data): ProgrammingLanguage
    {
        $model = new ProgrammingLanguage();
        $model->setRawAttributes($data);
        return $model;
    }

    /**
     * @param Collection<int, ProgrammingLanguage> $collection
     * @return array<int, array<string, mixed>>
     */
    public function toArrayCollection(Collection $collection): array
    {
        return $collection->map(fn (ProgrammingLanguage $lang): array => $this->toArray($lang))->all();
    }

    /**
     * @param array<int, array<string, mixed>> $data
     * @return Collection<int, ProgrammingLanguage>
     */
    public function fromArrayCollection(array $data): Collection
    {
        return collect($data)->map(fn (array $attrs): ProgrammingLanguage => $this->fromArray($attrs));
    }
}
