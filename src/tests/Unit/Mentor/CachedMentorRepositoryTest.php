<?php

declare(strict_types=1);

namespace Tests\Unit\Mentor;

use App\Domain\Mentor\Cache\MentorCacheInterface;
use App\Domain\Mentor\Mentor;
use App\Domain\Mentor\Repositories\MentorRepositoryInterface;
use App\Infrastructure\Mentor\Repositories\Cached\CachedMentorRepository;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class CachedMentorRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_get_list_by_user_id_returns_cached_value_when_cache_hit(): void
    {
        $cached = collect([MentorTestFactory::createMentor(1, 10)]);
        $cache = Mockery::mock(MentorCacheInterface::class);
        $cache->shouldReceive('getListByUserId')->once()->with(10)->andReturn($cached);
        $cache->shouldNotReceive('putListByUserId');

        $innerRepo = Mockery::mock(MentorRepositoryInterface::class);
        $innerRepo->shouldNotReceive('getListByUserId');

        $repo = new CachedMentorRepository($innerRepo, $cache);
        $result = $repo->getListByUserId(10);

        $this->assertSame($cached, $result);
    }

    public function test_get_list_by_user_id_calls_inner_and_puts_to_cache_when_cache_miss(): void
    {
        $fromDb = collect([MentorTestFactory::createMentor(1, 10)]);
        $cache = Mockery::mock(MentorCacheInterface::class);
        $cache->shouldReceive('getListByUserId')->once()->with(10)->andReturn(null);
        $cache->shouldReceive('putListByUserId')->once()->with(10, $fromDb);

        $innerRepo = Mockery::mock(MentorRepositoryInterface::class);
        $innerRepo->shouldReceive('getListByUserId')->once()->with(10)->andReturn($fromDb);

        $repo = new CachedMentorRepository($innerRepo, $cache);
        $result = $repo->getListByUserId(10);

        $this->assertSame($fromDb, $result);
    }

    public function test_find_by_id_delegates_to_inner(): void
    {
        $mentor = MentorTestFactory::createMentor(1, 10);
        $innerRepo = Mockery::mock(MentorRepositoryInterface::class);
        $innerRepo->shouldReceive('findById')->once()->with(1)->andReturn($mentor);

        $repo = new CachedMentorRepository($innerRepo, Mockery::mock(MentorCacheInterface::class));
        $this->assertSame($mentor, $repo->findById(1));
    }

    public function test_create_delegates_to_inner(): void
    {
        $mentor = MentorTestFactory::createMentor(1, 10);
        $innerRepo = Mockery::mock(MentorRepositoryInterface::class);
        $innerRepo->shouldReceive('create')->once()->with(['user_id' => 10])->andReturn($mentor);

        $repo = new CachedMentorRepository($innerRepo, Mockery::mock(MentorCacheInterface::class));
        $this->assertSame($mentor, $repo->create(['user_id' => 10]));
    }

    public function test_update_delegates_to_inner(): void
    {
        $mentor = MentorTestFactory::createMentor(1, 10);
        $innerRepo = Mockery::mock(MentorRepositoryInterface::class);
        $innerRepo->shouldReceive('update')->once()->with(1, ['name' => 'New'])->andReturn($mentor);

        $repo = new CachedMentorRepository($innerRepo, Mockery::mock(MentorCacheInterface::class));
        $this->assertSame($mentor, $repo->update(1, ['name' => 'New']));
    }

    public function test_delete_delegates_to_inner(): void
    {
        $innerRepo = Mockery::mock(MentorRepositoryInterface::class);
        $innerRepo->shouldReceive('delete')->once()->with(1);

        $repo = new CachedMentorRepository($innerRepo, Mockery::mock(MentorCacheInterface::class));
        $repo->delete(1);
    }
}
