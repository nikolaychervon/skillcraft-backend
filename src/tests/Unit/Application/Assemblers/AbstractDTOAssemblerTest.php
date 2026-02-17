<?php

namespace Tests\Unit\Application\Assemblers;

use PHPUnit\Framework\TestCase;
use App\Application\Shared\Exceptions\DTO\DTOAssemblyException;
use Tests\Fakes\DTOAssembler\TestUserDTO;
use Tests\Fakes\DTOAssembler\TestUserDTOAssembler;

class AbstractDTOAssemblerTest extends TestCase
{
    private TestUserDTOAssembler $assembler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assembler = new TestUserDTOAssembler();
    }

    public function test_it_creates_dto_from_camel_case_array(): void
    {
        $dto = $this->assembler->assemble([
            'firstName' => 'John',
            'lastName'  => 'Doe',
        ]);

        $this->assertInstanceOf(TestUserDTO::class, $dto);
        $this->assertEquals('John', $dto->firstName());
        $this->assertEquals('Doe', $dto->lastName());
        $this->assertNull($dto->middleName());
    }

    public function test_it_creates_dto_from_snake_case_array(): void
    {
        $dto = $this->assembler->assemble([
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
        ]);

        $this->assertEquals('Jane', $dto->firstName());
        $this->assertEquals('Doe', $dto->lastName());
    }

    public function test_it_uses_default_value_for_optional_fields(): void
    {
        $dto = $this->assembler->assemble([
            'firstName' => 'Mike',
            'lastName'  => 'Smith',
        ]);

        $this->assertNull($dto->middleName());
    }

    public function test_it_throws_exception_when_required_field_missing(): void
    {
        $this->expectException(DTOAssemblyException::class);

        $this->assembler->assemble([
            'firstName' => 'John',
        ]);
    }
}
