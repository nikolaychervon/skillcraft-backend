<?php

declare(strict_types=1);

namespace App\Support\Http;

enum HttpCode: int
{
    case Ok = 200;
    case Created = 201;
    case NoContent = 204;
    case BadRequest = 400;
    case Unauthorized = 401;
    case Forbidden = 403;
    case NotFound = 404;
    case ValidationError = 422;
    case TooManyRequests = 429;
    case InternalServerError = 500;
}
