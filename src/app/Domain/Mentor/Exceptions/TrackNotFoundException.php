<?php

declare(strict_types=1);

namespace App\Domain\Mentor\Exceptions;

use App\Application\Shared\Exceptions\ApiException;
use App\Support\Http\HttpCode;

final class TrackNotFoundException extends ApiException
{
    protected HttpCode $statusCode = HttpCode::NotFound;

    public function __construct(private int $specializationId, private int $programmingLanguageId)
    {
        parent::__construct();
    }

    /** @return array{specialization_id: int, programming_language_id: int} */
    public function getData(): array
    {
        return [
            'specialization_id' => $this->specializationId,
            'programming_language_id' => $this->programmingLanguageId,
        ];
    }
}
