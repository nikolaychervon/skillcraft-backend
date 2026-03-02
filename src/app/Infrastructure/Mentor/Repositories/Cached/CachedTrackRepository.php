<?php

declare(strict_types=1);

namespace App\Infrastructure\Mentor\Repositories\Cached;

use App\Domain\Mentor\Cache\TrackCacheInterface;
use App\Domain\Mentor\Repositories\TrackRepositoryInterface;
use App\Domain\Mentor\Track;

final class CachedTrackRepository implements TrackRepositoryInterface
{
    public function __construct(
        private TrackRepositoryInterface $trackRepository,
        private TrackCacheInterface $trackCache,
    ) {}

    public function getBySpecializationAndLanguage(int $specializationId, int $programmingLanguageId): ?Track
    {
        $cached = $this->trackCache->getForSpecializationAndLanguage($specializationId, $programmingLanguageId);
        if ($cached !== null) {
            return $cached;
        }

        $track = $this->trackRepository->getBySpecializationAndLanguage($specializationId, $programmingLanguageId);
        if ($track === null) {
            return null;
        }

        $this->trackCache->putForSpecializationAndLanguage($specializationId, $programmingLanguageId, $track);

        return $track;
    }
}
