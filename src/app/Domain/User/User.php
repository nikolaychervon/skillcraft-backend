<?php

declare(strict_types=1);

namespace App\Domain\User;

use DateTimeImmutable;

/** Доменная сущность пользователя (read-only). */
final readonly class User
{
    public function __construct(
        public int $id,
        public string $email,
        public string $password,
        public string $firstName,
        public string $lastName,
        public string $uniqueNickname,
        public ?string $middleName = null,
        public ?string $pendingEmail = null,
        public ?DateTimeImmutable $emailVerifiedAt = null,
    ) {}

    public function hasVerifiedEmail(): bool
    {
        return $this->emailVerifiedAt !== null;
    }
}
