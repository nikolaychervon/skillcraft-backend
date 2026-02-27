<?php

declare(strict_types=1);

namespace App\Application\User\Auth;

use App\Domain\User\Auth\Cache\PasswordResetTokensCacheInterface;
use App\Domain\User\Auth\Constants\AuthConstants;
use App\Domain\User\Auth\Exceptions\InvalidResetTokenException;
use App\Domain\User\Auth\Exceptions\PasswordResetFailedException;
use App\Domain\User\Auth\RequestData\ResetPasswordRequestData;
use App\Domain\User\Auth\Services\HashServiceInterface;
use App\Domain\User\Auth\Services\TokenServiceInterface;
use App\Domain\User\Auth\Services\TransactionServiceInterface;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\User;
use Illuminate\Support\Facades\Log;

final readonly class ResetPassword
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordResetTokensCacheInterface $passwordResetTokensCache,
        private HashServiceInterface $hashService,
        private TokenServiceInterface $tokenService,
        private TransactionServiceInterface $transactionService,
    ) {}

    /** @throws InvalidResetTokenException|PasswordResetFailedException|UserNotFoundException */
    public function run(ResetPasswordRequestData $data): string
    {
        $token = $this->passwordResetTokensCache->get($data->email);
        if ($token === null || $token !== $data->resetToken) {
            throw new InvalidResetTokenException;
        }

        $user = $this->userRepository->findByEmail($data->email);
        if ($user === null) {
            throw new UserNotFoundException(['email' => $data->email]);
        }

        return $this->doReset($user, $data);
    }

    /** @throws PasswordResetFailedException */
    private function doReset(User $user, ResetPasswordRequestData $data): string
    {
        try {
            return $this->transactionService->transaction(function () use ($user, $data): string {
                $hashedPassword = $this->hashService->make($data->password);
                $this->userRepository->updatePassword($user, $hashedPassword);
                $this->passwordResetTokensCache->delete($data->email);
                $this->tokenService->deleteAllTokens($user);

                return $this->tokenService->createAuthToken($user, AuthConstants::DEFAULT_TOKEN_NAME);
            });
        } catch (\Throwable $e) {
            Log::error('Password reset failed', [
                'email' => $data->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new PasswordResetFailedException(previous: $e);
        }
    }
}
