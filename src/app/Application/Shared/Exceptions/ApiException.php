<?php

declare(strict_types=1);

namespace App\Application\Shared\Exceptions;

use App\Support\Http\HttpCode;
use Exception;
use Throwable;

abstract class ApiException extends Exception
{
    protected HttpCode $statusCode = HttpCode::BadRequest;

    public function __construct(
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            $message ?? $this->getTranslatedMessage(),
            $code,
            $previous,
        );
    }

    public function getStatusCode(): HttpCode
    {
        return $this->statusCode;
    }

    /** @return array<string, mixed>|null */
    public function getData(): ?array
    {
        return null;
    }

    protected function getTranslatedMessage(): string
    {
        $key = 'exceptions.' . static::class;
        $message = __($key);

        return $message !== $key ? $message : $this->generateDefaultMessage();
    }

    protected function generateDefaultMessage(): string
    {
        $name = class_basename(static::class);
        $name = (string) preg_replace('/(?<! )(?<![A-Z])(?=[A-Z])/', ' ', $name);
        $name = str_replace('Exception', '', $name);

        return trim($name);
    }
}
