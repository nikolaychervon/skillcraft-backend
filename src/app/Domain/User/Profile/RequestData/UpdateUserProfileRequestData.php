<?php

declare(strict_types=1);

namespace App\Domain\User\Profile\RequestData;

use App\Domain\Shared\RequestData\BaseRequestData;

final readonly class UpdateUserProfileRequestData extends BaseRequestData
{
    public function __construct(
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $middleName = null,
        public ?string $uniqueNickname = null,
    ) {}
}
