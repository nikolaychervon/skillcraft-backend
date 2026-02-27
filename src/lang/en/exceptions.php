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
    IncorrectLoginDataException::class => 'Incorrect email address or password',
    NotFoundHttpException::class => 'Page not found',
    UnauthorizedException::class => 'Unauthorized',
    InvalidConfirmationLinkException::class => 'Invalid confirmation link',
    EmailAlreadyVerifiedException::class => 'Your email has already been confirmed. Log in to your account',
    InvalidResetTokenException::class => 'The password reset link is invalid or expired.',
    UserNotFoundException::class => 'User not found',
    PasswordResetFailedException::class => 'Failed to reset your password. Please try again.',
    TooManyRequestsHttpException::class => 'Too Many Attempts.',
    IncorrectCurrentPasswordException::class => 'Incorrect current password.',
];
