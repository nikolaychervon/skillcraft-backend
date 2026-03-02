<?php

declare(strict_types=1);

namespace Tests\Unit\Mentor;

use App\Application\Shared\Exceptions\Http\NotFoundHttpException;
use App\Domain\Mentor\Repositories\MentorRepositoryInterface;
use App\Application\Mentor\DeleteMentor;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class DeleteMentorActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_deletes_mentor_when_owned_by_user(): void
    {
        $mentor = MentorTestFactory::createMentor(1, 10);
        $repo = Mockery::mock(MentorRepositoryInterface::class);
        $repo->shouldReceive('findById')->once()->with(1)->andReturn($mentor);
        $repo->shouldReceive('delete')->once()->with(1);

        $action = new DeleteMentor($repo);
        $action->run(1, 10);
    }

    public function test_it_throws_not_found_when_mentor_does_not_exist(): void
    {
        $repo = Mockery::mock(MentorRepositoryInterface::class);
        $repo->shouldReceive('findById')->once()->with(999)->andReturn(null);
        $repo->shouldNotReceive('delete');

        $action = new DeleteMentor($repo);

        $this->expectException(NotFoundHttpException::class);
        $action->run(999, 10);
    }

    public function test_it_throws_not_found_when_mentor_belongs_to_another_user(): void
    {
        $mentor = MentorTestFactory::createMentor(1, 10);
        $repo = Mockery::mock(MentorRepositoryInterface::class);
        $repo->shouldReceive('findById')->once()->with(1)->andReturn($mentor);
        $repo->shouldNotReceive('delete');

        $action = new DeleteMentor($repo);

        $this->expectException(NotFoundHttpException::class);
        $action->run(1, 99);
    }
}
