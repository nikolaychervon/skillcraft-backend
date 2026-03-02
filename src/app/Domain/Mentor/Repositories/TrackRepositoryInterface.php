<?php

declare(strict_types=1);

namespace App\Domain\Mentor\Repositories;

use App\Domain\Mentor\Track;

interface TrackRepositoryInterface
{
    public function getBySpecializationAndLanguage(int $specializationId, int $programmingLanguageId): ?Track;
}
