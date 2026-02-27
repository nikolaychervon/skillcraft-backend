<?php

namespace Tests\Unit\Catalog\Specializations;

use App\Domain\Catalog\Specialization;
use App\Infrastructure\Catalog\Hydrators\SpecializationHydrator;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SpecializationHydratorTest extends TestCase
{
    private SpecializationHydrator $hydrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hydrator = new SpecializationHydrator;
    }

    public function test_to_array_returns_domain_attributes(): void
    {
        $specialization = new Specialization(1, 'backend', 'Backend');

        $result = $this->hydrator->toArray($specialization);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('key', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertSame(1, $result['id']);
        $this->assertSame('backend', $result['key']);
        $this->assertSame('Backend', $result['name']);
    }

    public function test_from_array_returns_domain_with_attributes(): void
    {
        $data = [
            'id' => 2,
            'key' => 'frontend',
            'name' => 'Frontend',
        ];

        $specialization = $this->hydrator->fromArray($data);

        $this->assertInstanceOf(Specialization::class, $specialization);
        $this->assertSame(2, $specialization->id);
        $this->assertSame('frontend', $specialization->key);
        $this->assertSame('Frontend', $specialization->name);
    }

    public function test_to_array_collection_serializes_collection(): void
    {
        $items = collect([
            new Specialization(1, 'a', 'A'),
            new Specialization(2, 'b', 'B'),
        ]);

        $result = $this->hydrator->toArrayCollection($items);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('a', $result[0]['key']);
        $this->assertSame(2, $result[1]['id']);
        $this->assertSame('b', $result[1]['key']);
    }

    public function test_from_array_collection_deserializes_to_collection(): void
    {
        $data = [
            ['id' => 10, 'key' => 'x', 'name' => 'X'],
            ['id' => 20, 'key' => 'y', 'name' => 'Y'],
        ];

        $collection = $this->hydrator->fromArrayCollection($data);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertInstanceOf(Specialization::class, $collection->first());
        $this->assertSame(10, $collection->first()->id);
        $this->assertSame(20, $collection->get(1)->id);
    }

    public function test_roundtrip_to_array_and_from_array_preserves_data(): void
    {
        $specialization = new Specialization(7, 'roundtrip', 'Roundtrip');

        $array = $this->hydrator->toArray($specialization);
        $restored = $this->hydrator->fromArray($array);

        $this->assertSame($specialization->id, $restored->id);
        $this->assertSame($specialization->key, $restored->key);
        $this->assertSame($specialization->name, $restored->name);
    }
}
