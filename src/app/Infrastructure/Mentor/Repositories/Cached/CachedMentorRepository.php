<?php

declare(strict_types=1);

namespace App\Infrastructure\Mentor\Repositories\Cached;

use App\Domain\Mentor\Cache\MentorCacheInterface;
use App\Domain\Mentor\Mentor;
use App\Domain\Mentor\Repositories\MentorRepositoryInterface;
use Illuminate\Support\Collection;

final class CachedMentorRepository implements MentorRepositoryInterface
{
    public function __construct(
        private MentorRepositoryInterface $mentorRepository,
        private MentorCacheInterface $mentorCache,
    ) {}

    public function findById(int $id): ?Mentor
    {
        return $this->mentorRepository->findById($id);
    }

    public function getListByUserId(int $userId): Collection
    {
        $mentors = $this->mentorCache->getListByUserId($userId);
        if ($mentors !== null) {
            return $mentors;
        }

        $mentors = $this->mentorRepository->getListByUserId($userId);
        $this->mentorCache->putListByUserId($userId, $mentors);

        return $mentors;
    }

    public function create(array $data): Mentor
    {
        return $this->mentorRepository->create($data);
    }

    public function update(int $id, array $data): Mentor
    {
        return $this->mentorRepository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->mentorRepository->delete($id);
    }
}
