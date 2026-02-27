<?php

namespace Tests\Unit\Catalog;

use App\Domain\Catalog\ProgrammingLanguage;
use App\Domain\Catalog\Specialization;
use App\Infrastructure\Catalog\Cache\CatalogCache;
use App\Infrastructure\Catalog\Hydrators\ProgrammingLanguageHydrator;
use App\Infrastructure\Catalog\Hydrators\SpecializationHydrator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CatalogCacheTest extends TestCase
{
    private CatalogCache $cache;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->cache = new CatalogCache(
            new SpecializationHydrator,
            new ProgrammingLanguageHydrator
        );
    }

    public function test_get_specializations_returns_null_when_empty(): void
    {
        $this->assertNull($this->cache->getSpecializations());
    }

    public function test_put_and_get_specializations_roundtrip(): void
    {
        $specializations = collect([
            new Specialization(1, 'backend', 'Backend'),
            new Specialization(2, 'frontend', 'Frontend'),
        ]);

        $this->cache->putSpecializations($specializations);
        $result = $this->cache->getSpecializations();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(Specialization::class, $result->first());
        $this->assertSame(1, $result->first()->id);
        $this->assertSame('backend', $result->first()->key);
        $this->assertSame(2, $result->get(1)->id);
        $this->assertSame('frontend', $result->get(1)->key);
    }

    public function test_delete_specializations_clears_cache(): void
    {
        $specializations = collect([
            new Specialization(1, 'x', 'X'),
        ]);
        $this->cache->putSpecializations($specializations);
        $this->assertNotNull($this->cache->getSpecializations());

        $this->cache->deleteSpecializations();

        $this->assertNull($this->cache->getSpecializations());
    }

    public function test_get_specialization_languages_returns_null_when_empty(): void
    {
        $this->assertNull($this->cache->getSpecializationLanguages(1));
    }

    public function test_put_and_get_specialization_languages_roundtrip(): void
    {
        $specializationId = 10;
        $languages = collect([
            new ProgrammingLanguage(1, 'php', 'PHP'),
            new ProgrammingLanguage(2, 'js', 'JavaScript'),
        ]);

        $this->cache->putSpecializationLanguages($specializationId, $languages);
        $result = $this->cache->getSpecializationLanguages($specializationId);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(ProgrammingLanguage::class, $result->first());
        $this->assertSame(1, $result->first()->id);
        $this->assertSame('php', $result->first()->key);
        $this->assertSame(2, $result->get(1)->id);
    }

    public function test_delete_specialization_languages_clears_only_that_specialization(): void
    {
        $langs1 = collect([
            new ProgrammingLanguage(1, 'a', 'A'),
        ]);
        $langs2 = collect([
            new ProgrammingLanguage(2, 'b', 'B'),
        ]);

        $this->cache->putSpecializationLanguages(1, $langs1);
        $this->cache->putSpecializationLanguages(2, $langs2);

        $this->cache->deleteSpecializationLanguages(1);

        $this->assertNull($this->cache->getSpecializationLanguages(1));
        $this->assertNotNull($this->cache->getSpecializationLanguages(2));
        $this->assertCount(1, $this->cache->getSpecializationLanguages(2));
    }
}
