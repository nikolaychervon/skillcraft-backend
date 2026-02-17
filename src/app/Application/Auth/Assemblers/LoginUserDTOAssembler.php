<?php

namespace App\Application\Auth\Assemblers;

use App\Application\Shared\Assemblers\AbstractDTOAssembler;
use App\Domain\Auth\DTO\LoginUserDTO;

/**
 * @extends AbstractDTOAssembler<LoginUserDTO>
 */
class LoginUserDTOAssembler extends AbstractDTOAssembler
{
    /**
     * @return string
     */
    protected function getDtoClass(): string
    {
        return LoginUserDTO::class;
    }
}
