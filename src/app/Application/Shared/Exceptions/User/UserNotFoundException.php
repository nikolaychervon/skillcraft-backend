<?php

namespace App\Application\Shared\Exceptions\User;

use App\Application\Shared\Exceptions\ApiException;
use App\Http\Responses\ApiResponse;

class UserNotFoundException extends ApiException
{
    protected $code = ApiResponse::HTTP_NOT_FOUND;

    public function __construct(private array $searchData)
    {
        parent::__construct();
    }

    /**
     * @return ?array{search_data: array}
     */
    public function getData(): ?array
    {
        return [
            'search_data' => $this->searchData,
        ];
    }
}
