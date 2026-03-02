<?php

declare(strict_types=1);

namespace App\Infrastructure\Mentor\Cache;

use App\Domain\Mentor\Cache\TrackCacheInterface;
use App\Domain\Mentor\Track;
use App\Infrastructure\Mentor\Hydrators\TrackHydrator;
use Illuminate\Support\Facades\Cache;

final class TrackCache implements TrackCacheInterface
{
    private const string KEY_FOR_SPEC_AND_LANG = 'specialization.%d.language.%d.track';

    private const int FOR_SPEC_AND_LANG_TTL = 60;

    public function __construct(
        private TrackHydrator $trackHydrator
    ) {}

    public function getForSpecializationAndLanguage(int $specializationId, int $programmingLanguageId): ?Track
    {
        $key = sprintf(self::KEY_FOR_SPEC_AND_LANG, $specializationId, $programmingLanguageId);
        $raw = Cache::get($key);
        if (!is_string($raw)) {
            return null;
        }

        $cached = json_decode($raw, true);
        if (!is_array($cached)) {
            return null;
        }

        return $this->trackHydrator->fromArray($cached);
    }

    public function putForSpecializationAndLanguage(
        int $specializationId,
        int $programmingLanguageId,
        Track $track
    ): void {
        $key = sprintf(self::KEY_FOR_SPEC_AND_LANG, $specializationId, $programmingLanguageId);
        $payload = $this->trackHydrator->toArray($track);
        Cache::put($key, json_encode($payload), self::FOR_SPEC_AND_LANG_TTL);
    }
}
