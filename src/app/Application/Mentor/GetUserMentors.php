<?php

declare(strict_types=1);

namespace App\Application\Mentor;

use App\Domain\Mentor\Mentor;
use App\Domain\Mentor\Repositories\MentorRepositoryInterface;
use Illuminate\Support\Collection;

final readonly class GetUserMentors
{
    public function __construct(
        private MentorRepositoryInterface $mentorRepository,
    ) {}

    /** @return Collection<int, Mentor> */
    public function run(int $userId): Collection
    {
        return $this->mentorRepository->getListByUserId($userId);
    }
}
