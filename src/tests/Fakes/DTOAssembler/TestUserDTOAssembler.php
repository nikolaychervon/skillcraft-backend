<?php

namespace Tests\Fakes\DTOAssembler;

use App\Application\Shared\Assemblers\AbstractDTOAssembler;

class TestUserDTOAssembler extends AbstractDTOAssembler
{
    protected function getDtoClass(): string
    {
        return TestUserDTO::class;
    }
}
