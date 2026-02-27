<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\Http\Requests\Base\AuthenticatedRequest;
use App\Http\Requests\Rules\ValidationRules;

class UpdateProfileRequest extends AuthenticatedRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('middle_name') && trim((string) $this->input('middle_name')) === '') {
            $this->merge(['middle_name' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'unique_nickname' => ValidationRules::uniqueNicknameForProfile($this->getDomainUser()->id),
        ];
    }
}
