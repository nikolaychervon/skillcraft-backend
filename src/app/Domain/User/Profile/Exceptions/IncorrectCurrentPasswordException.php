<?php

declare(strict_types=1);

namespace App\Domain\User\Profile\Exceptions;

use App\Application\Shared\Exceptions\ApiException;
use App\Support\Http\HttpCode;

final class IncorrectCurrentPasswordException extends ApiException
{
    protected HttpCode $statusCode = HttpCode::ValidationError;
}
