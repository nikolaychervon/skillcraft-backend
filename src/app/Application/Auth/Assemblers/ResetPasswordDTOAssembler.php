<?php

namespace App\Application\Auth\Assemblers;

use App\Application\Shared\Assemblers\AbstractDTOAssembler;
use App\Domain\Auth\DTO\ResetPasswordDTO;

/**
 * @extends AbstractDTOAssembler<ResetPasswordDTO>
 */
class ResetPasswordDTOAssembler extends AbstractDTOAssembler
{
    /**
     * @return string
     */
    protected function getDtoClass(): string
    {
        return ResetPasswordDTO::class;
    }
}
