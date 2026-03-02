<?php

declare(strict_types=1);

namespace App\Domain\Mentor\Repositories;

use App\Domain\Mentor\Mentor;
use Illuminate\Support\Collection;

interface MentorRepositoryInterface
{
    public function findById(int $id): ?Mentor;

    /** @return Collection<int, Mentor> */
    public function getListByUserId(int $userId): Collection;

    public function create(array $data): Mentor;

    public function delete(int $id): void;
}
