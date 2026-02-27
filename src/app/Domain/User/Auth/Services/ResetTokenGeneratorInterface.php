<?php

declare(strict_types=1);

namespace App\Domain\User\Auth\Services;

interface ResetTokenGeneratorInterface
{
    public function generate(int $length): string;
}
