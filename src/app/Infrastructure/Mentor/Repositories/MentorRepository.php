<?php

declare(strict_types=1);

namespace App\Infrastructure\Mentor\Repositories;

use App\Domain\Mentor\Mentor;
use App\Infrastructure\Mentor\Mappers\MentorMapper;
use App\Models\Mentor as MentorModel;
use App\Domain\Mentor\Repositories\MentorRepositoryInterface;
use Illuminate\Support\Collection;

final class MentorRepository implements MentorRepositoryInterface
{
    public function __construct(
        private MentorMapper $mapper,
    ) {}

    public function findById(int $id): ?Mentor
    {
        $model = MentorModel::query()
            ->with(['track.specialization', 'track.programmingLanguage'])
            ->find($id);

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    /** @inheritDoc */
    public function getListByUserId(int $userId): Collection
    {
        $models = MentorModel::query()
            ->with(['track.specialization', 'track.programmingLanguage'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn (MentorModel $model): Mentor => $this->mapper->toDomain($model));
    }

    public function create(array $data): Mentor
    {
        $mentorModel = MentorModel::query()->create($data);
        $mentorModel->load(['track.specialization', 'track.programmingLanguage']);

        return $this->mapper->toDomain($mentorModel);
    }

    public function delete(int $id): void
    {
        $model = MentorModel::query()->find($id);
        if ($model !== null) {
            $model->delete();
        }
    }
}
