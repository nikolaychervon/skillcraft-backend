<?php

declare(strict_types=1);

namespace App\Infrastructure\Mentor\Listeners;

use App\Domain\Mentor\Cache\MentorCacheInterface;
use App\Domain\Mentor\Events\MentorChanged;

final class InvalidateUserMentorsCache
{
    public function __construct(
        private MentorCacheInterface $mentorCache,
    ) {}

    public function handle(MentorChanged $event): void
    {
        $this->mentorCache->forgetListByUserId($event->userId);
    }
}
