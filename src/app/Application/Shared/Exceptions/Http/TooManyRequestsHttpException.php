<?php

namespace App\Application\Shared\Exceptions\Http;

use App\Application\Shared\Exceptions\ApiException;
use App\Http\Responses\ApiResponse;

class TooManyRequestsHttpException extends ApiException
{
    protected $code = ApiResponse::HTTP_TOO_MANY_REQUESTS;
}
