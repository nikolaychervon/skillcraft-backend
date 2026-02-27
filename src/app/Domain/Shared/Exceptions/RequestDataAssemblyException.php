<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

class RequestDataAssemblyException extends \RuntimeException
{
    public static function requestDataClassNotFound(string $requestDataClass, \Throwable $previous): self
    {
        return new self(
            "RequestData class '{$requestDataClass}' cannot be instantiated.",
            previous: $previous
        );
    }

    public static function missingField(string $field, string $requestDataClass): self
    {
        return new self(
            "Required field '{$field}' is missing for RequestData {$requestDataClass}."
        );
    }
}
