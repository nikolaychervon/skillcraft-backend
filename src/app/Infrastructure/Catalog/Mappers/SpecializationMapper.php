<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog\Mappers;

use App\Domain\Catalog\Specialization;
use App\Models\Specialization as SpecializationModel;

final class SpecializationMapper
{
    public function toDomain(SpecializationModel $model): Specialization
    {
        return new Specialization(
            id: $model->id,
            key: $model->key,
            name: $model->name,
        );
    }
}
