<?php

use App\Application\Shared\Exceptions\Http\NotFoundHttpException;
use App\Application\Shared\Exceptions\Http\TooManyRequestsHttpException;
use App\Application\Shared\Exceptions\Http\UnauthorizedException;
use App\Domain\User\Auth\Exceptions\IncorrectLoginDataException;
use App\Domain\User\Auth\Exceptions\InvalidResetTokenException;
use App\Domain\User\Auth\Exceptions\PasswordResetFailedException;
use App\Domain\User\Exceptions\Email\EmailAlreadyVerifiedException;
use App\Domain\User\Exceptions\Email\InvalidConfirmationLinkException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\Profile\Exceptions\IncorrectCurrentPasswordException;

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
    IncorrectCurrentPasswordException::class => 'Текущий пароль указан неверно.',
];
