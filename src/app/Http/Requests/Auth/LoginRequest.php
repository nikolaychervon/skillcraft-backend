<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\Rules\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|string|email|max:255',
            'password' => ValidationRules::passwordLogin(),
        ];
    }
}
