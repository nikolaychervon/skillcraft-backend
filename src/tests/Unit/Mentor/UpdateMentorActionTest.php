<?php

declare(strict_types=1);

namespace Tests\Unit\Mentor;

use App\Application\Shared\Exceptions\Http\NotFoundHttpException;
use App\Domain\Mentor\Mentor;
use App\Domain\Mentor\Repositories\MentorRepositoryInterface;
use App\Domain\Mentor\RequestData\UpdateMentorRequestData;
use App\Application\Mentor\UpdateMentor;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class UpdateMentorActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_updates_and_returns_mentor_when_owned_by_user(): void
    {
        $existing = MentorTestFactory::createMentor(1, 10, 'Old Name', 'old-slug');
        $updated = MentorTestFactory::createMentor(1, 10, 'New Name', 'new-slug');
        $data = new UpdateMentorRequestData(
            name: 'New Name',
            slug: 'new-slug',
            useNameToCallMe: true,
            targetLevel: 'Senior',
            mentorPersona: 'strict',
            howToCallMe: null,
        );

        $repo = Mockery::mock(MentorRepositoryInterface::class);
        $repo->shouldReceive('findById')->once()->with(1)->andReturn($existing);
        $repo->shouldReceive('update')
            ->once()
            ->with(1, Mockery::on(function (array $payload): bool {
                return $payload['name'] === 'New Name'
                    && $payload['slug'] === 'new-slug'
                    && $payload['target_level'] === 'Senior'
                    && $payload['mentor_persona'] === 'strict';
            }))
            ->andReturn($updated);

        $action = new UpdateMentor($repo);
        $result = $action->run($data, 1, 10);

        $this->assertSame($updated, $result);
    }

    public function test_it_throws_not_found_when_mentor_does_not_exist(): void
    {
        $data = new UpdateMentorRequestData(
            name: 'Name',
            slug: 'slug',
            useNameToCallMe: true,
            targetLevel: 'Middle',
            mentorPersona: 'friendly',
        );
        $repo = Mockery::mock(MentorRepositoryInterface::class);
        $repo->shouldReceive('findById')->once()->with(999)->andReturn(null);
        $repo->shouldNotReceive('update');

        $action = new UpdateMentor($repo);

        $this->expectException(NotFoundHttpException::class);
        $action->run($data, 999, 10);
    }

    public function test_it_throws_not_found_when_mentor_belongs_to_another_user(): void
    {
        $mentor = MentorTestFactory::createMentor(1, 10);
        $data = new UpdateMentorRequestData(
            name: 'Name',
            slug: 'slug',
            useNameToCallMe: true,
            targetLevel: 'Middle',
            mentorPersona: 'friendly',
        );
        $repo = Mockery::mock(MentorRepositoryInterface::class);
        $repo->shouldReceive('findById')->once()->with(1)->andReturn($mentor);
        $repo->shouldNotReceive('update');

        $action = new UpdateMentor($repo);

        $this->expectException(NotFoundHttpException::class);
        $action->run($data, 1, 99);
    }
}
