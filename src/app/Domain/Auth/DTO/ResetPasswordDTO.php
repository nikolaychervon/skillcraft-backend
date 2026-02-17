<?php

namespace App\Domain\Auth\DTO;

use App\Application\Shared\DTO\BaseDTO;

readonly class ResetPasswordDTO extends BaseDTO
{
    /**
     * @param string $email
     * @param string $resetToken
     * @param string $password
     */
    public function __construct(
        private string $email,
        private string $resetToken,
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
    public function getResetToken(): string
    {
        return $this->resetToken;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
