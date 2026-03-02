<?php

declare(strict_types=1);

namespace App\Domain\User\Auth\Exceptions;

use App\Application\Shared\Exceptions\ApiException;
use App\Support\Http\HttpCode;

final class IncorrectLoginDataException extends ApiException
{
    protected HttpCode $statusCode = HttpCode::Unauthorized;
}
