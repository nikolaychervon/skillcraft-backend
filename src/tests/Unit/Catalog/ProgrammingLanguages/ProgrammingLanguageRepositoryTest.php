<?php

namespace Tests\Unit\Catalog\ProgrammingLanguages;

use App\Infrastructure\Catalog\Mappers\ProgrammingLanguageMapper;
use App\Infrastructure\Catalog\Repositories\ProgrammingLanguageRepository;
use App\Models\ProgrammingLanguage;
use App\Models\Specialization;
use App\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgrammingLanguageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProgrammingLanguageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProgrammingLanguageRepository(new ProgrammingLanguageMapper);
    }

    public function test_get_by_specialization_id_returns_languages_through_tracks(): void
    {
        $spec = Specialization::create(['key' => 'backend', 'name' => 'Backend']);
        $php = ProgrammingLanguage::create(['key' => 'php', 'name' => 'PHP']);
        $go = ProgrammingLanguage::create(['key' => 'go', 'name' => 'Go']);

        Track::create([
            'key' => 'backend-php',
            'specialization_id' => $spec->id,
            'programming_language_id' => $php->id,
            'name' => 'Backend PHP',
        ]);
        Track::create([
            'key' => 'backend-go',
            'specialization_id' => $spec->id,
            'programming_language_id' => $go->id,
            'name' => 'Backend Go',
        ]);

        $result = $this->repository->getBySpecializationId($spec->id);

        $this->assertCount(2, $result);
        $keys = $result->pluck('key')->sort()->values()->all();
        $this->assertSame(['go', 'php'], $keys);
    }

    public function test_get_by_specialization_id_returns_empty_when_specialization_not_found(): void
    {
        $result = $this->repository->getBySpecializationId(99999);

        $this->assertTrue($result->isEmpty());
    }

    public function test_get_by_specialization_id_returns_empty_when_no_tracks(): void
    {
        $spec = Specialization::create(['key' => 'empty', 'name' => 'Empty']);

        $result = $this->repository->getBySpecializationId($spec->id);

        $this->assertTrue($result->isEmpty());
    }
}
