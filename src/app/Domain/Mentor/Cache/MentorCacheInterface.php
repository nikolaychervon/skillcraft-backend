<?php

declare(strict_types=1);

namespace App\Domain\Mentor\Cache;

use App\Domain\Mentor\Mentor;
use Illuminate\Support\Collection;

interface MentorCacheInterface
{
    /** @return Collection<int, Mentor>|null */
    public function getListByUserId(int $userId): ?Collection;

    /** @param Collection<int, Mentor> $mentors */
    public function putListByUserId(int $userId, Collection $mentors): void;

    public function forgetListByUserId(int $userId): void;
}
