<?php

declare(strict_types=1);

namespace App\Infrastructure\Mentor\Hydrators;

use App\Domain\Mentor\Track;
use App\Infrastructure\Catalog\Hydrators\ProgrammingLanguageHydrator;
use App\Infrastructure\Catalog\Hydrators\SpecializationHydrator;
use DateTimeImmutable;

final class TrackHydrator
{
    public function __construct(
        private SpecializationHydrator $specializationHydrator,
        private ProgrammingLanguageHydrator $programmingLanguageHydrator,
    ) {}

    public function toArray(Track $track): array
    {
        return [
            'id' => $track->id,
            'key' => $track->key,
            'name' => $track->name,
            'created_at' => $track->createdAt->format('Y-m-d H:i:s'),
            'specialization' => $this->specializationHydrator->toArray($track->specialization),
            'programming_language' => $this->programmingLanguageHydrator->toArray($track->programmingLanguage),
        ];
    }

    public function fromArray(array $data): Track
    {
        return new Track(
            id: $data['id'],
            key: $data['key'],
            name: $data['name'],
            createdAt: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['created_at']),
            specialization: $this->specializationHydrator->fromArray($data['specialization']),
            programmingLanguage: $this->programmingLanguageHydrator->fromArray($data['programming_language']),
        );
    }
}
