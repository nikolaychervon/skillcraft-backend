<?php

use App\Notifications\User\PasswordResetNotification;
use App\Notifications\User\VerifyEmailForRegisterNotification;

return [
    VerifyEmailForRegisterNotification::class => [
        'subject' => 'Подтверждение регистрации на GradeUP',
        'greeting' => 'Здравствуйте, :name!',
        'lines_1' => [
            'Вы получили это письмо, потому что зарегистрировались на платформе GradeUP.',
            'Для активации аккаунта подтвердите ваш email:',
        ],
        'action' => [
            'text' => 'Подтвердить email',
            'url' => ':verification_url',
        ],
        'lines_2' => [
            'Ссылка действительна в течение 60 минут.',
            'Если вы не регистрировались, просто проигнорируйте это письмо.',
        ],
    ],
    PasswordResetNotification::class => [
        'subject' => 'Сброс пароля на GradeUP',
        'greeting' => 'Здравствуйте!',
        'lines_1' => [
            'Вы получили это письмо, потому что мы получили запрос на сброс пароля для вашей учётной записи.',
        ],
        'action' => [
            'text' => 'Сбросить пароль',
            'url' => ':reset_url',
        ],
        'lines_2' => [
            'Ссылка действительна в течение :expires_minutes минут.',
            'Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.',
        ],
    ],
];
