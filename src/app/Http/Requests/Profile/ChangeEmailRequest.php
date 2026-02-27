<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\Http\Requests\Base\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class ChangeEmailRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $userId = $this->getDomainUser()->id;

        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
                Rule::unique('users', 'pending_email')->ignore($userId),
            ],
        ];
    }
}
