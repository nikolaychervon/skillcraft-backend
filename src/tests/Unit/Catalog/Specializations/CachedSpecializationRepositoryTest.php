<?php

namespace Tests\Unit\Catalog\Specializations;

use App\Domain\Catalog\Cache\CatalogCacheInterface;
use App\Domain\Catalog\Repositories\SpecializationRepositoryInterface;
use App\Domain\Catalog\Specialization;
use App\Infrastructure\Catalog\Repositories\Cached\CachedSpecializationRepository;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class CachedSpecializationRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_get_all_returns_cached_value_when_cache_hit(): void
    {
        $cached = collect([
            new Specialization(1, 'cached', 'Cached'),
        ]);

        $cache = Mockery::mock(CatalogCacheInterface::class);
        $cache->shouldReceive('getSpecializations')
            ->once()
            ->andReturn($cached);
        $cache->shouldNotReceive('putSpecializations');

        $innerRepo = Mockery::mock(SpecializationRepositoryInterface::class);
        $innerRepo->shouldNotReceive('getAll');

        $repo = new CachedSpecializationRepository($innerRepo, $cache);

        $result = $repo->getAll();

        $this->assertSame($cached, $result);
    }

    public function test_get_all_calls_inner_and_puts_to_cache_when_cache_miss(): void
    {
        $fromDb = collect([
            new Specialization(1, 'backend', 'Backend'),
        ]);

        $cache = Mockery::mock(CatalogCacheInterface::class);
        $cache->shouldReceive('getSpecializations')
            ->once()
            ->andReturn(null);
        $cache->shouldReceive('putSpecializations')
            ->once()
            ->with(Mockery::on(fn (Collection $c): bool => $c->count() === 1 && $c->first()->key === 'backend'));

        $innerRepo = Mockery::mock(SpecializationRepositoryInterface::class);
        $innerRepo->shouldReceive('getAll')
            ->once()
            ->andReturn($fromDb);

        $repo = new CachedSpecializationRepository($innerRepo, $cache);

        $result = $repo->getAll();

        $this->assertSame($fromDb, $result);
    }

    public function test_find_by_id_delegates_to_inner_without_cache(): void
    {
        $specialization = new Specialization(42, 'frontend', 'Frontend');

        $cache = Mockery::mock(CatalogCacheInterface::class);
        $cache->shouldNotReceive('getSpecializations');
        $cache->shouldNotReceive('getSpecializationLanguages');

        $innerRepo = Mockery::mock(SpecializationRepositoryInterface::class);
        $innerRepo->shouldReceive('findById')
            ->once()
            ->with(42)
            ->andReturn($specialization);

        $repo = new CachedSpecializationRepository($innerRepo, $cache);

        $result = $repo->findById(42);

        $this->assertSame($specialization, $result);
    }

    public function test_find_by_id_returns_null_when_inner_returns_null(): void
    {
        $innerRepo = Mockery::mock(SpecializationRepositoryInterface::class);
        $innerRepo->shouldReceive('findById')->once()->with(999)->andReturn(null);

        $repo = new CachedSpecializationRepository($innerRepo, Mockery::mock(CatalogCacheInterface::class));

        $this->assertNull($repo->findById(999));
    }
}
