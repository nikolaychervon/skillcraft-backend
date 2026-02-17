<?php

namespace App\Application\Shared\Exceptions\User\Email;

use App\Application\Shared\Exceptions\ApiException;
use App\Http\Responses\ApiResponse;

class EmailAlreadyVerifiedException extends ApiException
{
    protected $code = ApiResponse::HTTP_BAD_REQUEST;
}
