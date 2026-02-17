<?php

namespace App\Domain\Auth\DTO;

use App\Application\Shared\DTO\BaseDTO;

readonly class LoginUserDTO extends BaseDTO
{
    /**
     * @param string $email
     * @param string $password
     */
    public function __construct(
        private string $email,
        private string $password,
    ) {
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
