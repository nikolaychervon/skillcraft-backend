<?php

declare(strict_types=1);

namespace Tests\Unit\Mentor;

use App\Domain\Mentor\Mentor;
use App\Domain\Mentor\Repositories\MentorRepositoryInterface;
use App\Application\Mentor\GetUserMentors;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class GetUserMentorsActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_returns_mentors_from_repository(): void
    {
        $mentors = collect([
            MentorTestFactory::createMentor(1, 10),
            MentorTestFactory::createMentor(2, 10),
        ]);

        $repo = Mockery::mock(MentorRepositoryInterface::class);
        $repo->shouldReceive('getListByUserId')
            ->once()
            ->with(10)
            ->andReturn($mentors);

        $action = new GetUserMentors($repo);
        $result = $action->run(10);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertSame($mentors, $result);
    }

    public function test_it_returns_empty_collection_when_user_has_no_mentors(): void
    {
        $repo = Mockery::mock(MentorRepositoryInterface::class);
        $repo->shouldReceive('getListByUserId')
            ->once()
            ->with(5)
            ->andReturn(collect());

        $action = new GetUserMentors($repo);
        $result = $action->run(5);

        $this->assertTrue($result->isEmpty());
    }
}
