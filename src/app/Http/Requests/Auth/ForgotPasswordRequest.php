<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Traits\HasEmail;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    use HasEmail;

    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
        ];
    }
}
