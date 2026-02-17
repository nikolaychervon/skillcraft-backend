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
    IncorrectLoginDataException::class => 'Incorrect email address or password',
    NotFoundHttpException::class => 'Page not found',
    UnauthorizedException::class => 'Unauthorized',
    InvalidConfirmationLinkException::class => 'Invalid confirmation link',
    EmailAlreadyVerifiedException::class => 'Your email has already been confirmed. Log in to your account',
    InvalidResetTokenException::class => 'The password reset link is invalid or expired.',
    UserNotFoundException::class => 'User not found',
    PasswordResetFailedException::class => 'Failed to reset your password. Please try again.',
    TooManyRequestsHttpException::class => 'Too Many Attempts.',
];
