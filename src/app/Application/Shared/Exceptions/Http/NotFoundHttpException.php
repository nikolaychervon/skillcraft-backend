<?php

declare(strict_types=1);

namespace App\Application\Shared\Exceptions\Http;

use App\Application\Shared\Exceptions\ApiException;
use App\Support\Http\HttpCode;

final class NotFoundHttpException extends ApiException
{
    protected HttpCode $statusCode = HttpCode::NotFound;
}
