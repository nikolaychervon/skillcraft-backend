<?php

namespace App\Domain\Auth\DTO;

use App\Application\Shared\DTO\BaseDTO;

readonly class ResendEmailDTO extends BaseDTO
{
    /**
     * @param string $email
     */
    public function __construct(
        private string $email,
    ) {
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}
