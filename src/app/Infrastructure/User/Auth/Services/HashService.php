<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth\Services;

use App\Domain\User\Auth\Services\HashServiceInterface;
use Illuminate\Contracts\Hashing\Hasher;

class HashService implements HashServiceInterface
{
    public function __construct(
        private readonly Hasher $hasher
    ) {}

    public function make(string $password): string
    {
        return $this->hasher->make($password);
    }

    public function check(string $password, string $hashedPassword): bool
    {
        return $this->hasher->check($password, $hashedPassword);
    }
}
