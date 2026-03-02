<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth\Services;

use App\Domain\User\Auth\Services\TransactionServiceInterface;
use Illuminate\Support\Facades\DB;

final class TransactionService implements TransactionServiceInterface
{
    public function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
