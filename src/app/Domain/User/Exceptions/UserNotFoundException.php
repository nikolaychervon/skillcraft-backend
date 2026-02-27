<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use App\Application\Shared\Exceptions\ApiException;
use App\Support\Http\HttpCode;

class UserNotFoundException extends ApiException
{
    protected HttpCode $statusCode = HttpCode::NotFound;

    /**
     * @param  array<string, mixed>  $searchData
     */
    public function __construct(private readonly array $searchData)
    {
        parent::__construct();
    }

    /** @return array{search_data: array<string, mixed>}|null */
    public function getData(): ?array
    {
        return ['search_data' => $this->searchData];
    }
}
