<?php

declare(strict_types=1);

namespace App\Infrastructure\Mentor\Mappers;

use App\Domain\Mentor\Track;
use App\Infrastructure\Catalog\Mappers\ProgrammingLanguageMapper;
use App\Infrastructure\Catalog\Mappers\SpecializationMapper;
use App\Models\Track as TrackModel;
use DateTimeImmutable;

final class TrackMapper
{
    public function __construct(
        private SpecializationMapper $specializationMapper,
        private ProgrammingLanguageMapper $programmingLanguageMapper,
    ) {}

    public function toDomain(TrackModel $model): Track
    {
        return new Track(
            id: $model->id,
            key: $model->key,
            name: $model->name,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            specialization: $this->specializationMapper->toDomain($model->specialization),
            programmingLanguage: $this->programmingLanguageMapper->toDomain($model->programmingLanguage),
        );
    }
}
