<?php

declare(strict_types=1);

namespace Tests\Unit\Mentor;

use App\Application\Shared\Constants\LevelsConstants;
use App\Application\Shared\Constants\MentorPersonaConstants;
use App\Domain\Mentor\Repositories\MentorRepositoryInterface;
use App\Models\Mentor as MentorModel;
use App\Models\Track;
use App\Models\User;
use Database\Seeders\MentorSeeder;
use Database\Seeders\ProgrammingLanguageSeeder;
use Database\Seeders\SpecializationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesVerifiedUser;
use Tests\TestCase;

class MentorRepositoryTest extends TestCase
{
    use CreatesVerifiedUser;
    use RefreshDatabase;

    private MentorRepositoryInterface $repository;
    private User $user;
    private Track $track;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SpecializationSeeder::class);
        $this->seed(ProgrammingLanguageSeeder::class);
        $this->seed(MentorSeeder::class);
        $this->user = $this->createVerifiedUser(['email' => 'mentor-repo@example.com']);
        $this->track = Track::query()->firstOrFail();
        $this->repository = $this->app->make(MentorRepositoryInterface::class);
    }

    public function test_find_by_id_returns_null_when_mentor_does_not_exist(): void
    {
        $this->assertNull($this->repository->findById(99999));
    }

    public function test_find_by_id_returns_domain_mentor_when_exists(): void
    {
        $model = MentorModel::query()->create([
            'user_id' => $this->user->id,
            'track_id' => $this->track->id,
            'name' => 'Repo Test',
            'slug' => 'repo-test-mentor',
            'target_level' => LevelsConstants::MIDDLE,
            'current_level' => LevelsConstants::UNSETTED,
            'how_to_call_me' => null,
            'use_name_to_call_me' => true,
            'mentor_persona' => MentorPersonaConstants::FRIENDLY,
        ]);

        $mentor = $this->repository->findById($model->id);

        $this->assertNotNull($mentor);
        $this->assertSame($model->id, $mentor->id);
        $this->assertSame($this->user->id, $mentor->userId);
        $this->assertSame('Repo Test', $mentor->name);
        $this->assertSame('repo-test-mentor', $mentor->slug);
        $this->assertSame($this->track->id, $mentor->track->id);
    }

    public function test_get_list_by_user_id_returns_mentors_ordered_by_created_at_desc(): void
    {
        MentorModel::query()->create([
            'user_id' => $this->user->id,
            'track_id' => $this->track->id,
            'name' => 'First',
            'slug' => 'first-mentor-'.uniqid(),
            'target_level' => LevelsConstants::MIDDLE,
            'current_level' => LevelsConstants::UNSETTED,
            'how_to_call_me' => null,
            'use_name_to_call_me' => true,
            'mentor_persona' => MentorPersonaConstants::NEUTRAL,
        ]);
        MentorModel::query()->create([
            'user_id' => $this->user->id,
            'track_id' => $this->track->id,
            'name' => 'Second',
            'slug' => 'second-mentor-'.uniqid(),
            'target_level' => LevelsConstants::SENIOR,
            'current_level' => LevelsConstants::UNSETTED,
            'how_to_call_me' => null,
            'use_name_to_call_me' => true,
            'mentor_persona' => MentorPersonaConstants::STRICT,
        ]);

        $list = $this->repository->getListByUserId($this->user->id);

        $this->assertCount(2, $list);
        $names = $list->map(fn ($m) => $m->name)->values()->all();
        $this->assertContains('First', $names);
        $this->assertContains('Second', $names);
        // Ordered by created_at desc: first element has created_at >= last
        $this->assertGreaterThanOrEqual(
            $list->first()->createdAt->getTimestamp(),
            $list->last()->createdAt->getTimestamp(),
        );
    }

    public function test_get_list_by_user_id_returns_empty_for_user_without_mentors(): void
    {
        $otherUser = $this->createVerifiedUser(['email' => 'other@example.com']);
        $list = $this->repository->getListByUserId($otherUser->id);
        $this->assertCount(0, $list);
    }

    public function test_create_persists_mentor_and_returns_domain(): void
    {
        $data = [
            'user_id' => $this->user->id,
            'track_id' => $this->track->id,
            'name' => 'Created Mentor',
            'slug' => 'created-mentor-'.uniqid(),
            'target_level' => LevelsConstants::JUNIOR,
            'how_to_call_me' => 'Hey',
            'use_name_to_call_me' => false,
            'mentor_persona' => MentorPersonaConstants::FRIENDLY,
        ];

        $mentor = $this->repository->create($data);

        $this->assertDatabaseHas('mentors', [
            'name' => 'Created Mentor',
            'user_id' => $this->user->id,
            'track_id' => $this->track->id,
        ]);
        $this->assertSame($data['name'], $mentor->name);
        $this->assertSame($data['slug'], $mentor->slug);
        $this->assertSame($this->user->id, $mentor->userId);
    }

    public function test_update_modifies_mentor_and_returns_domain(): void
    {
        $model = MentorModel::query()->create([
            'user_id' => $this->user->id,
            'track_id' => $this->track->id,
            'name' => 'Original',
            'slug' => 'original-'.uniqid(),
            'target_level' => LevelsConstants::MIDDLE,
            'current_level' => LevelsConstants::UNSETTED,
            'how_to_call_me' => null,
            'use_name_to_call_me' => true,
            'mentor_persona' => MentorPersonaConstants::NEUTRAL,
        ]);

        $updated = $this->repository->update($model->id, [
            'name' => 'Updated Name',
            'slug' => $model->slug,
            'target_level' => LevelsConstants::SENIOR,
            'current_level' => LevelsConstants::MIDDLE,
            'how_to_call_me' => 'Call me',
            'use_name_to_call_me' => false,
            'mentor_persona' => MentorPersonaConstants::STRICT,
        ]);

        $this->assertSame('Updated Name', $updated->name);
        $this->assertSame(LevelsConstants::SENIOR, $updated->targetLevel);
        $model->refresh();
        $this->assertSame('Updated Name', $model->name);
    }

    public function test_delete_removes_mentor(): void
    {
        $model = MentorModel::query()->create([
            'user_id' => $this->user->id,
            'track_id' => $this->track->id,
            'name' => 'To Delete',
            'slug' => 'to-delete-'.uniqid(),
            'target_level' => LevelsConstants::MIDDLE,
            'current_level' => LevelsConstants::UNSETTED,
            'how_to_call_me' => null,
            'use_name_to_call_me' => true,
            'mentor_persona' => MentorPersonaConstants::NEUTRAL,
        ]);

        $this->repository->delete($model->id);

        $this->assertDatabaseMissing('mentors', ['id' => $model->id]);
    }
}
