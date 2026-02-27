<?php

declare(strict_types=1);

namespace App\Application\User\Auth;

use App\Domain\User\Auth\RequestData\ResendEmailRequestData;
use App\Domain\User\Auth\Services\NotificationServiceInterface;
use App\Domain\User\Exceptions\Email\EmailAlreadyVerifiedException;
use App\Domain\User\Repositories\UserRepositoryInterface;

/**
 * Переотправка письма подтверждения email на указанный адрес.
 * Ничего не делает, если пользователь не найден; исключение — если email уже подтверждён.
 */
final readonly class ResendVerificationEmail
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private NotificationServiceInterface $notificationService,
    ) {}

    /** @throws EmailAlreadyVerifiedException */
    public function run(ResendEmailRequestData $data): void
    {
        $user = $this->userRepository->findByEmail($data->email);
        if ($user === null) {
            return;
        }

        if ($user->hasVerifiedEmail()) {
            throw new EmailAlreadyVerifiedException;
        }

        $this->notificationService->sendEmailVerificationNotification($user);
    }
}
