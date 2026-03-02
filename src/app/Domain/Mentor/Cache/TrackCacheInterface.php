<?php

declare(strict_types=1);

namespace App\Domain\Mentor\Cache;

use App\Domain\Mentor\Track;

interface TrackCacheInterface
{
    public function getForSpecializationAndLanguage(int $specializationId, int $programmingLanguageId): ?Track;

    public function putForSpecializationAndLanguage(
        int $specializationId,
        int $programmingLanguageId,
        Track $track
    ): void;
}
