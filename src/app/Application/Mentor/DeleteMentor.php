<?php

declare(strict_types=1);

namespace App\Application\Mentor;

use App\Application\Shared\Exceptions\Http\NotFoundHttpException;
use App\Domain\Mentor\Repositories\MentorRepositoryInterface;

final readonly class DeleteMentor
{
    public function __construct(
        private MentorRepositoryInterface $mentorRepository,
    ) {}

    /**
     * @throws NotFoundHttpException
     */
    public function run(int $mentorId, int $userId): void
    {
        $mentor = $this->mentorRepository->findById($mentorId);
        if ($mentor === null || $mentor->userId !== $userId) {
            throw new NotFoundHttpException;
        }

        $this->mentorRepository->delete($mentorId);
    }
}
