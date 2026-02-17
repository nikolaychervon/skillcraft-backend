<?php

namespace App\Domain\Auth\DTO;

use App\Application\Shared\DTO\BaseDTO;

readonly class CreatingUserDTO extends BaseDTO
{
    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $uniqueNickname
     * @param string $password
     * @param string|null $middleName
     */
    public function __construct(
        private string $firstName,
        private string $lastName,
        private string $email,
        private string $uniqueNickname,
        private string $password,
        private ?string $middleName = null,
    ) {
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getMiddleName(): ?string
    {
        return $this->middleName;
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
    public function getUniqueNickname(): string
    {
        return $this->uniqueNickname;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
