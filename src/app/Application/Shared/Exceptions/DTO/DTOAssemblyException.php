<?php

namespace App\Application\Shared\Exceptions\DTO;

class DTOAssemblyException extends \RuntimeException
{
    /**
     * @param string $dtoClass
     * @param \Throwable $previous
     * @return self
     */
    public static function dtoClassNotFound(string $dtoClass, \Throwable $previous): self
    {
        return new self(
            "DTO class '{$dtoClass}' cannot be instantiated.",
            previous: $previous
        );
    }

    /**
     * @param string $field
     * @param string $dtoClass
     * @return self
     */
    public static function missingField(string $field, string $dtoClass): self
    {
        return new self(
            "Required field '{$field}' is missing for DTO {$dtoClass}."
        );
    }
}
