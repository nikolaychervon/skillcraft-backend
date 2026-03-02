<?php

declare(strict_types=1);

namespace App\Infrastructure\Mentor\Mappers;

use App\Domain\Mentor\Mentor;
use App\Models\Mentor as MentorModel;
use DateTimeImmutable;

final class MentorMapper
{
    public function __construct(
        private TrackMapper $trackMapper,
    ) {}

    public function toDomain(MentorModel $model): Mentor
    {
        return new Mentor(
            id: $model->id,
            userId: $model->user_id,
            name: $model->name,
            slug: $model->slug,
            targetLevel: $model->target_level,
            currentLevel: $model->current_level,
            howToCallMe: $model->how_to_call_me ?? '',
            useNameToCallMe: $model->use_name_to_call_me,
            mentorPersona: $model->mentor_persona,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            track: $this->trackMapper->toDomain($model->track),
        );
    }
}
