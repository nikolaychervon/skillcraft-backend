<?php

declare(strict_types=1);

namespace App\Http\Requests\Rules;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class ValidationRules
{
    public const int NICKNAME_MIN_LENGTH = 3;

    public const int NICKNAME_MAX_LENGTH = 20;

    /** Длина пароля. */
    public const int PASSWORD_MIN_LENGTH = 8;

    public const int PASSWORD_MAX_LENGTH = 30;

    /** Регулярное выражение для уникального никнейма (латиница, цифры, подчёркивание, дефис). */
    public const string NICKNAME_REGEX = '/^[a-zA-Z0-9_-]+$/';

    /**
     * @return array<int, mixed>
     */
    public static function passwordRequired(): array
    {
        return [
            'required',
            Password::min(self::PASSWORD_MIN_LENGTH)
                ->max(self::PASSWORD_MAX_LENGTH)
                ->numbers()
                ->symbols(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function passwordLogin(): array
    {
        return [
            'required',
            Password::min(self::PASSWORD_MIN_LENGTH)->max(self::PASSWORD_MAX_LENGTH),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function uniqueNickname(): array
    {
        return [
            'required',
            'min:'.self::NICKNAME_MIN_LENGTH,
            'max:'.self::NICKNAME_MAX_LENGTH,
            'regex:'.self::NICKNAME_REGEX,
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function uniqueNicknameForProfile(int $userId): array
    {
        return [
            ...self::uniqueNickname(),
            Rule::unique('users', 'unique_nickname')->ignore($userId),
        ];
    }
}
