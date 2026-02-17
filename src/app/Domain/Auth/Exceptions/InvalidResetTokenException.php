<?php

namespace App\Domain\Auth\Exceptions;

use App\Application\Shared\Exceptions\ApiException;
use App\Http\Responses\ApiResponse;

class InvalidResetTokenException extends ApiException
{
    protected $code = ApiResponse::HTTP_VALIDATION_ERROR;
}
