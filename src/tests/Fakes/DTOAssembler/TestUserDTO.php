<?php

namespace Tests\Fakes\DTOAssembler;

use App\Application\Shared\DTO\BaseDTO;

readonly class TestUserDTO extends BaseDTO
{
    public function __construct(
        private string $firstName,
        private string $lastName,
        private ?string $middleName = null,
    ) {}

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function lastName(): string
    {
        return $this->lastName;
    }

    public function middleName(): ?string
    {
        return $this->middleName;
    }
}
