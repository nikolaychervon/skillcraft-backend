<?php

declare(strict_types=1);

namespace App\Domain\User\Profile\RequestData;

use App\Domain\Shared\RequestData\BaseRequestData;

final readonly class ChangeUserPasswordRequestData extends BaseRequestData
{
    public function __construct(
        public string $oldPassword,
        public string $password,
    ) {}
}
