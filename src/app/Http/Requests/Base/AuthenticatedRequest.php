<?php

declare(strict_types=1);

namespace App\Http\Requests\Base;

use App\Domain\User\User as DomainUser;
use App\Infrastructure\User\Mappers\UserMapper;
use Illuminate\Foundation\Http\FormRequest;

class AuthenticatedRequest extends FormRequest
{
    private ?DomainUser $domainUser = null;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function getDomainUser(): DomainUser
    {
        if ($this->domainUser === null) {
            $this->domainUser = app(UserMapper::class)->toDomain($this->user());
        }

        return $this->domainUser;
    }
}
