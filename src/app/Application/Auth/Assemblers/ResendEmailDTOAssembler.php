<?php

namespace App\Application\Auth\Assemblers;

use App\Application\Shared\Assemblers\AbstractDTOAssembler;
use App\Domain\Auth\DTO\ResendEmailDTO;

/**
 * @extends AbstractDTOAssembler<ResendEmailDTO>
 */
class ResendEmailDTOAssembler extends AbstractDTOAssembler
{
    /**
     * @return string
     */
    protected function getDtoClass(): string
    {
        return ResendEmailDTO::class;
    }
}
