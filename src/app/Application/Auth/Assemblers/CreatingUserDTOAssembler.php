<?php

namespace App\Application\Auth\Assemblers;

use App\Application\Shared\Assemblers\AbstractDTOAssembler;
use App\Domain\Auth\DTO\CreatingUserDTO;

/**
 * @extends AbstractDTOAssembler<CreatingUserDTO>
 */
class CreatingUserDTOAssembler extends AbstractDTOAssembler
{
    /**
     * @return string
     */
    protected function getDtoClass(): string
    {
        return CreatingUserDTO::class;
    }
}
