<?php

declare(strict_types=1);

namespace App\Infrastructure\Mentor\Cache;

use App\Domain\Mentor\Cache\MentorCacheInterface;
use App\Infrastructure\Mentor\Hydrators\MentorHydrator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class MentorCache implements MentorCacheInterface
{
    private const string KEY_USER_MENTORS = 'mentors.user.%d.list';

    private const int USER_MENTORS_TTL = 300;

    public function __construct(
        private MentorHydrator $mentorHydrator,
    ) {}

    /** @inheritDoc */
    public function getListByUserId(int $userId): ?Collection
    {
        $key = sprintf(self::KEY_USER_MENTORS, $userId);
        $raw = Cache::get($key);
        if (!is_string($raw)) {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $this->mentorHydrator->fromArrayCollection($decoded);
    }

    /** @inheritDoc */
    public function putListByUserId(int $userId, Collection $mentors): void
    {
        $key = sprintf(self::KEY_USER_MENTORS, $userId);
        $payload = $this->mentorHydrator->toArrayCollection($mentors);
        Cache::put($key, json_encode($payload), self::USER_MENTORS_TTL);
    }

    public function forgetListByUserId(int $userId): void
    {
        $key = sprintf(self::KEY_USER_MENTORS, $userId);
        Cache::forget($key);
    }
}
