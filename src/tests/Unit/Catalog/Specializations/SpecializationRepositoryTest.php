<?php

namespace Tests\Unit\Catalog\Specializations;

use App\Domain\Catalog\Specialization;
use App\Infrastructure\Catalog\Mappers\SpecializationMapper;
use App\Infrastructure\Catalog\Repositories\SpecializationRepository;
use App\Models\Specialization as SpecializationModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpecializationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SpecializationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SpecializationRepository(new SpecializationMapper);
    }

    public function test_get_all_returns_specializations_ordered_by_name(): void
    {
        SpecializationModel::create(['key' => 'backend', 'name' => 'Backend']);
        SpecializationModel::create(['key' => 'frontend', 'name' => 'Frontend']);
        SpecializationModel::create(['key' => 'android', 'name' => 'Android']);

        $result = $this->repository->getAll();

        $this->assertCount(3, $result);
        $names = $result->pluck('name')->all();
        $this->assertSame(['Android', 'Backend', 'Frontend'], $names);
    }

    public function test_get_all_returns_empty_collection_when_no_specializations(): void
    {
        $result = $this->repository->getAll();

        $this->assertTrue($result->isEmpty());
    }

    public function test_find_by_id_returns_specialization_when_exists(): void
    {
        $spec = SpecializationModel::create(['key' => 'backend', 'name' => 'Backend']);

        $result = $this->repository->findById($spec->id);

        $this->assertNotNull($result);
        $this->assertInstanceOf(Specialization::class, $result);
        $this->assertSame($spec->id, $result->id);
        $this->assertSame('backend', $result->key);
        $this->assertSame('Backend', $result->name);
    }

    public function test_find_by_id_returns_null_when_not_exists(): void
    {
        $result = $this->repository->findById(99999);

        $this->assertNull($result);
    }
}
