<?php

namespace App\Application\Shared\Exceptions\Http;

use App\Application\Shared\Exceptions\ApiException;
use App\Http\Responses\ApiResponse;

class NotFoundHttpException extends ApiException
{
    protected $code = ApiResponse::HTTP_NOT_FOUND;
}
