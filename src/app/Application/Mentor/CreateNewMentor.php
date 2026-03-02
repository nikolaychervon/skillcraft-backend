<?php

declare(strict_types=1);

namespace App\Application\Mentor;

use App\Domain\Mentor\Exceptions\TrackNotFoundException;
use App\Domain\Mentor\Mentor;
use App\Domain\Mentor\Repositories\MentorRepositoryInterface;
use App\Domain\Mentor\Repositories\TrackRepositoryInterface;
use App\Domain\Mentor\RequestData\CreateNewMentorRequestData;

final readonly class CreateNewMentor
{
    public function __construct(
        private MentorRepositoryInterface $mentorRepository,
        private TrackRepositoryInterface $trackRepository,
    ) {}

    public function run(CreateNewMentorRequestData $requestData, int $userId): Mentor
    {
        $track = $this->trackRepository->getBySpecializationAndLanguage(
            specializationId: $requestData->specializationId,
            programmingLanguageId: $requestData->programmingLanguageId
        );

        if ($track === null) {
            throw new TrackNotFoundException(
                specializationId: $requestData->specializationId,
                programmingLanguageId: $requestData->programmingLanguageId
            );
        }

        return $this->mentorRepository->create([
            'user_id' => $userId,
            'track_id' => $track->id,
            'name' => $requestData->name,
            'slug' => $requestData->slug,
            'target_level' => $requestData->targetLevel,
            'how_to_call_me' => $requestData->howToCallMe,
            'use_name_to_call_me' => $requestData->useNameToCallMe,
            'mentor_persona' => $requestData->mentorPersona,
        ]);
    }
}
