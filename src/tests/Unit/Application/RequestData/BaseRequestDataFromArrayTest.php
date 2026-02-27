<?php

declare(strict_types=1);

namespace Tests\Unit\Application\RequestData;

use App\Domain\Shared\Exceptions\RequestDataAssemblyException;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\RequestDataAssembler\TestNoConstructorRequestData;
use Tests\Fakes\RequestDataAssembler\TestUserRequestData;

class BaseRequestDataFromArrayTest extends TestCase
{
    public function test_creates_request_data_from_camel_case_array(): void
    {
        $data = TestUserRequestData::fromArray([
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        $this->assertInstanceOf(TestUserRequestData::class, $data);
        $this->assertSame('John', $data->firstName);
        $this->assertSame('Doe', $data->lastName);
        $this->assertNull($data->middleName);
    }

    public function test_creates_request_data_from_snake_case_array(): void
    {
        $data = TestUserRequestData::fromArray([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $this->assertSame('Jane', $data->firstName);
        $this->assertSame('Doe', $data->lastName);
    }

    public function test_uses_default_value_for_optional_fields(): void
    {
        $data = TestUserRequestData::fromArray([
            'firstName' => 'Mike',
            'lastName' => 'Smith',
        ]);

        $this->assertNull($data->middleName);
    }

    public function test_throws_when_required_field_missing(): void
    {
        $this->expectException(RequestDataAssemblyException::class);

        TestUserRequestData::fromArray([
            'firstName' => 'John',
        ]);
    }

    public function test_throws_when_request_data_has_no_constructor(): void
    {
        $this->expectException(RequestDataAssemblyException::class);

        TestNoConstructorRequestData::fromArray([]);
    }
}
