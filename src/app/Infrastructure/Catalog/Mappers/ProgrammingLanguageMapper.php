<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Mappers;

use App\Domain\Catalog\ProgrammingLanguage;
use App\Models\ProgrammingLanguage as ProgrammingLanguageModel;

final class ProgrammingLanguageMapper
{
    public function toDomain(ProgrammingLanguageModel $model): ProgrammingLanguage
    {
        return new ProgrammingLanguage(
            id: $model->id,
            key: $model->key,
            name: $model->name,
        );
    }
}
