<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'reset_token' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)->max(30)->numbers()->symbols()],
        ];
    }
}
