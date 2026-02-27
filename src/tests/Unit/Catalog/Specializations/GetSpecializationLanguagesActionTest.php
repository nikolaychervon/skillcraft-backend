<?php

namespace Tests\Unit\Catalog\Specializations;

use App\Application\Catalog\GetSpecializationLanguages;
use App\Domain\Catalog\ProgrammingLanguage;
use App\Domain\Catalog\Repositories\ProgrammingLanguageRepositoryInterface;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class GetSpecializationLanguagesActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_returns_languages_for_specialization_from_repository(): void
    {
        $specializationId = 5;
        $languages = collect([
            new ProgrammingLanguage(1, 'php', 'PHP'),
            new ProgrammingLanguage(2, 'js', 'JavaScript'),
        ]);

        $repo = Mockery::mock(ProgrammingLanguageRepositoryInterface::class);
        $repo->shouldReceive('getBySpecializationId')
            ->once()
            ->with($specializationId)
            ->andReturn($languages);

        $action = new GetSpecializationLanguages($repo);

        $result = $action->run($specializationId);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertSame($languages, $result);
    }

    public function test_it_returns_empty_collection_when_no_languages(): void
    {
        $repo = Mockery::mock(ProgrammingLanguageRepositoryInterface::class);
        $repo->shouldReceive('getBySpecializationId')
            ->once()
            ->with(99)
            ->andReturn(collect());

        $action = new GetSpecializationLanguages($repo);

        $result = $action->run(99);

        $this->assertTrue($result->isEmpty());
    }
}
