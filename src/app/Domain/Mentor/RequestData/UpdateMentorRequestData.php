<?php

declare(strict_types=1);

namespace App\Domain\Mentor\RequestData;

use App\Domain\Shared\RequestData\BaseRequestData;

final readonly class UpdateMentorRequestData extends BaseRequestData
{
    public function __construct(
        public string $name,
        public string $slug,
        public bool $useNameToCallMe,
        public string $targetLevel,
        public string $mentorPersona,
        public ?string $howToCallMe = null,
    ) {}
}
