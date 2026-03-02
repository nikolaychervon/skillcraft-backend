<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions\Email;

use App\Application\Shared\Exceptions\ApiException;
use App\Support\Http\HttpCode;

final class EmailAlreadyVerifiedException extends ApiException
{
    protected HttpCode $statusCode = HttpCode::BadRequest;
}
