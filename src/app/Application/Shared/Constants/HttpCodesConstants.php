<?php

declare(strict_types=1);

namespace App\Application\Shared\Constants;

class HttpCodesConstants
{
    public const int
        HTTP_OK = 200,
        HTTP_CREATED = 201,
        HTTP_BAD_REQUEST = 400,
        HTTP_NOT_AUTHORIZED = 401,
        HTTP_FORBIDDEN = 403,
        HTTP_NOT_FOUND = 404,
        HTTP_VALIDATION_ERROR = 422,
        HTTP_TOO_MANY_REQUESTS = 429,
        HTTP_SERVER_ERROR = 500;
}
