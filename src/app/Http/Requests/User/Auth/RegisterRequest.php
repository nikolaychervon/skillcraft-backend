<?php

namespace App\Http\Requests\User\Auth;

use App\Http\Requests\Traits\HasEmail;
use App\Http\Requests\Traits\HasPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    use HasEmail, HasPassword;

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
                'required',
                'min:3',
                'max:20',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('users')->where(function ($query) {
                    $query->whereNot('email', $this->input('email'));
                }),
            ],
            'password' => ['required', Password::min(8)->max(30)->numbers()->symbols()],
        ];
    }

    public function getFirstName(): string
    {
        return $this->input('first_name');
    }

    public function getLastName(): string
    {
        return $this->input('last_name');
    }

    public function getMiddleName(): ?string
    {
        return $this->input('middle_name');
    }

    public function getUniqueNickname(): string
    {
        return $this->input('unique_nickname');
    }
}
