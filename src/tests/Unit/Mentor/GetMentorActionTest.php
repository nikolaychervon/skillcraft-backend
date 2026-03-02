<?php

declare(strict_types=1);

namespace Tests\Unit\Mentor;

use App\Application\Shared\Exceptions\Http\NotFoundHttpException;
use App\Domain\Mentor\Mentor;
use App\Domain\Mentor\Repositories\MentorRepositoryInterface;
use App\Application\Mentor\GetMentor;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class GetMentorActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_returns_mentor_when_found_and_owned_by_user(): void
    {
        $mentor = MentorTestFactory::createMentor(1, 10);
        $repo = Mockery::mock(MentorRepositoryInterface::class);
        $repo->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($mentor);

        $action = new GetMentor($repo);
        $result = $action->run(1, 10);

        $this->assertSame($mentor, $result);
        $this->assertSame(1, $result->id);
        $this->assertSame(10, $result->userId);
    }

    public function test_it_throws_not_found_when_mentor_does_not_exist(): void
    {
        $repo = Mockery::mock(MentorRepositoryInterface::class);
        $repo->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $action = new GetMentor($repo);

        $this->expectException(NotFoundHttpException::class);
        $action->run(999, 10);
    }

    public function test_it_throws_not_found_when_mentor_belongs_to_another_user(): void
    {
        $mentor = MentorTestFactory::createMentor(1, 10);
        $repo = Mockery::mock(MentorRepositoryInterface::class);
        $repo->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($mentor);

        $action = new GetMentor($repo);

        $this->expectException(NotFoundHttpException::class);
        $action->run(1, 99);
    }
}
