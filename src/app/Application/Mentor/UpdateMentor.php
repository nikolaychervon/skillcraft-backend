<?php

declare(strict_types=1);

namespace App\Application\Mentor;

use App\Application\Shared\Exceptions\Http\NotFoundHttpException;
use App\Domain\Mentor\Mentor;
use App\Domain\Mentor\Repositories\MentorRepositoryInterface;
use App\Domain\Mentor\RequestData\UpdateMentorRequestData;

final readonly class UpdateMentor
{
    public function __construct(
        private MentorRepositoryInterface $mentorRepository,
    ) {}

    /**
     * @throws NotFoundHttpException
     */
    public function run(UpdateMentorRequestData $data, int $mentorId, int $userId): Mentor
    {
        $mentor = $this->mentorRepository->findById($mentorId);
        if ($mentor === null || $mentor->userId !== $userId) {
            throw new NotFoundHttpException;
        }

        return $this->mentorRepository->update($mentorId, [
            'name' => $data->name,
            'slug' => $data->slug,
            'target_level' => $data->targetLevel,
            'how_to_call_me' => $data->howToCallMe,
            'use_name_to_call_me' => $data->useNameToCallMe,
            'mentor_persona' => $data->mentorPersona,
        ]);
    }
}
