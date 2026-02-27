<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth\Services;

use App\Domain\User\Auth\Services\ResetTokenGeneratorInterface;
use Illuminate\Support\Str;

class ResetTokenGenerator implements ResetTokenGeneratorInterface
{
    public function generate(int $length): string
    {
        return Str::random($length);
    }
}
