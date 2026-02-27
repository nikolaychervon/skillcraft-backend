<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\Http\Requests\Base\AuthenticatedRequest;
use App\Http\Requests\Rules\ValidationRules;

class ChangePasswordRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'old_password' => 'required|string',
            'password' => ['confirmed', ...ValidationRules::passwordRequired()],
        ];
    }
}
