<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\Rules\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'reset_token' => 'required|string',
            'password' => ['confirmed', ...ValidationRules::passwordRequired()],
        ];
    }
}
