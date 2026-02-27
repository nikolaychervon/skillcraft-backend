<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\Rules\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->whereNotNull('email_verified_at'),
            ],
            'unique_nickname' => [
                ...ValidationRules::uniqueNickname(),
                Rule::unique('users')->where(function ($query) {
                    $query->whereNot('email', $this->input('email'));
                }),
            ],
            'password' => ValidationRules::passwordRequired(),
        ];
    }
}
