<?php

declare(strict_types=1);

namespace App\Domain\User\Auth\RequestData;

use App\Domain\Shared\RequestData\BaseRequestData;

final readonly class CreatingUserRequestData extends BaseRequestData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $uniqueNickname,
        public string $password,
        public ?string $middleName = null,
    ) {}
}
