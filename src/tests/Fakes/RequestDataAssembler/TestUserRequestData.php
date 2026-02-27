<?php

declare(strict_types=1);

namespace Tests\Fakes\RequestDataAssembler;

use App\Domain\Shared\RequestData\BaseRequestData;

final readonly class TestUserRequestData extends BaseRequestData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public ?string $middleName = null,
    ) {}
}
