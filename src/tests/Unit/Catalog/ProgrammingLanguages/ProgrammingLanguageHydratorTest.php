<?php

namespace Tests\Unit\Catalog\ProgrammingLanguages;

use App\Domain\Catalog\ProgrammingLanguage;
use App\Infrastructure\Catalog\Hydrators\ProgrammingLanguageHydrator;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ProgrammingLanguageHydratorTest extends TestCase
{
    private ProgrammingLanguageHydrator $hydrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hydrator = new ProgrammingLanguageHydrator;
    }

    public function test_to_array_returns_domain_attributes(): void
    {
        $language = new ProgrammingLanguage(1, 'php', 'PHP');

        $result = $this->hydrator->toArray($language);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('key', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertSame(1, $result['id']);
        $this->assertSame('php', $result['key']);
        $this->assertSame('PHP', $result['name']);
    }

    public function test_from_array_returns_domain_with_attributes(): void
    {
        $data = [
            'id' => 2,
            'key' => 'javascript',
            'name' => 'JavaScript',
        ];

        $language = $this->hydrator->fromArray($data);

        $this->assertInstanceOf(ProgrammingLanguage::class, $language);
        $this->assertSame(2, $language->id);
        $this->assertSame('javascript', $language->key);
        $this->assertSame('JavaScript', $language->name);
    }

    public function test_to_array_collection_serializes_collection(): void
    {
        $items = collect([
            new ProgrammingLanguage(1, 'php', 'PHP'),
            new ProgrammingLanguage(2, 'js', 'JS'),
        ]);

        $result = $this->hydrator->toArrayCollection($items);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('php', $result[0]['key']);
        $this->assertSame(2, $result[1]['id']);
    }

    public function test_from_array_collection_deserializes_to_collection(): void
    {
        $data = [
            ['id' => 10, 'key' => 'go', 'name' => 'Go'],
            ['id' => 20, 'key' => 'rust', 'name' => 'Rust'],
        ];

        $collection = $this->hydrator->fromArrayCollection($data);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertInstanceOf(ProgrammingLanguage::class, $collection->first());
        $this->assertSame(10, $collection->first()->id);
        $this->assertSame(20, $collection->get(1)->id);
    }

    public function test_roundtrip_to_array_and_from_array_preserves_data(): void
    {
        $language = new ProgrammingLanguage(7, 'roundtrip', 'Roundtrip Lang');

        $array = $this->hydrator->toArray($language);
        $restored = $this->hydrator->fromArray($array);

        $this->assertSame($language->id, $restored->id);
        $this->assertSame($language->key, $restored->key);
        $this->assertSame($language->name, $restored->name);
    }
}
