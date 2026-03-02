<?php

declare(strict_types=1);

namespace App\Domain\Mentor\RequestData;

use App\Domain\Shared\RequestData\BaseRequestData;

final readonly class CreateNewMentorRequestData extends BaseRequestData
{
    public function __construct(
        public int $specializationId,
        public int $programmingLanguageId,
        public string $name,
        public string $slug,
        public bool $useNameToCallMe,
        public string $targetLevel,
        public string $mentorPersona,
        public ?string $howToCallMe = null,
    ) {}
}
