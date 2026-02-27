<?php

namespace Tests\Unit\Catalog\Specializations;

use App\Application\Catalog\GetSpecializations;
use App\Domain\Catalog\Repositories\SpecializationRepositoryInterface;
use App\Domain\Catalog\Specialization;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class GetSpecializationsActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_returns_all_specializations_from_repository(): void
    {
        $specializations = collect([
            new Specialization(1, 'frontend', 'Frontend'),
            new Specialization(2, 'backend', 'Backend'),
        ]);

        $repo = Mockery::mock(SpecializationRepositoryInterface::class);
        $repo->shouldReceive('getAll')
            ->once()
            ->withNoArgs()
            ->andReturn($specializations);

        $action = new GetSpecializations($repo);

        $result = $action->run();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertSame($specializations, $result);
    }

    public function test_it_returns_empty_collection_when_repository_returns_empty(): void
    {
        $repo = Mockery::mock(SpecializationRepositoryInterface::class);
        $repo->shouldReceive('getAll')
            ->once()
            ->andReturn(collect());

        $action = new GetSpecializations($repo);

        $result = $action->run();

        $this->assertTrue($result->isEmpty());
    }
}
