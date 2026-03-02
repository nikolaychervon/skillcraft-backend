<?php

declare(strict_types=1);

namespace App\Infrastructure\Mentor\Repositories;

use App\Domain\Mentor\Repositories\TrackRepositoryInterface;
use App\Domain\Mentor\Track;
use App\Infrastructure\Mentor\Mappers\TrackMapper;
use App\Models\Track as TrackModel;

final class TrackRepository implements TrackRepositoryInterface
{
    public function __construct(
        private TrackMapper $mapper,
    ) {}

    public function getBySpecializationAndLanguage(int $specializationId, int $programmingLanguageId): ?Track
    {
        $trackModel = TrackModel::query()
            ->with(['specialization', 'programmingLanguage'])
            ->where([
                'specialization_id' => $specializationId,
                'programming_language_id' => $programmingLanguageId,
            ])
            ->first();

        return $trackModel === null ? null : $this->mapper->toDomain($trackModel);
    }
}
