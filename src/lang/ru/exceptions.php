<?php

use App\Application\Shared\Exceptions\Http\NotFoundHttpException;
use App\Application\Shared\Exceptions\Http\TooManyRequestsHttpException;
use App\Application\Shared\Exceptions\Http\UnauthorizedException;
use App\Application\Shared\Exceptions\User\Email\EmailAlreadyVerifiedException;
use App\Application\Shared\Exceptions\User\Email\InvalidConfirmationLinkException;
use App\Application\Shared\Exceptions\User\UserNotFoundException;
use App\Domain\Auth\Exceptions\IncorrectLoginDataException;
use App\Domain\Auth\Exceptions\InvalidResetTokenException;
use App\Domain\Auth\Exceptions\PasswordResetFailedException;

return [
    IncorrectLoginDataException::class => 'Неверный email или пароль',
    NotFoundHttpException::class => 'Страница не найдена',
    UnauthorizedException::class => 'Вы не авторизованы',
    InvalidConfirmationLinkException::class => 'Неверная ссылка подтверждения',
    EmailAlreadyVerifiedException::class => 'Email уже подтверждён. Войдите в аккаунт',
    InvalidResetTokenException::class => 'Ссылка для сброса пароля недействительна или истекла',
    UserNotFoundException::class => 'Пользователь не найден',
    PasswordResetFailedException::class => 'Не удалось сбросить пароль. Пожалуйста, попробуйте снова.',
    TooManyRequestsHttpException::class => 'Слишком много частых запросов.',
];
