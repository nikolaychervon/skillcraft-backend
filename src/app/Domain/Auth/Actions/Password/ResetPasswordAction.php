<?php

namespace App\Domain\Auth\Actions\Password;

use App\Application\Shared\Exceptions\User\UserNotFoundException;
use App\Domain\Auth\Cache\PasswordResetTokensCacheInterface;
use App\Domain\Auth\DTO\ResetPasswordDTO;
use App\Domain\Auth\Exceptions\InvalidResetTokenException;
use App\Domain\Auth\Exceptions\PasswordResetFailedException;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordAction
{
    /**
     * @param UserRepositoryInterface $userRepository
     * @param PasswordResetTokensCacheInterface $passwordResetTokensCache
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordResetTokensCacheInterface $passwordResetTokensCache,
    ) {
    }

    /**
     * @param ResetPasswordDTO $resetPasswordDTO
     * @return string
     *
     * @throws InvalidResetTokenException
     * @throws PasswordResetFailedException
     * @throws UserNotFoundException
     */
    public function run(ResetPasswordDTO $resetPasswordDTO): string
    {
        /** @var ?string $token */
        $token = $this->passwordResetTokensCache->get($resetPasswordDTO->getEmail());
        if (!$token || $token !== $resetPasswordDTO->getResetToken()) {
            throw new InvalidResetTokenException();
        }

        $user = $this->userRepository->findByEmail($resetPasswordDTO->getEmail());
        if (!$user instanceof User) {
            throw new UserNotFoundException(['email' => $resetPasswordDTO->getEmail()]);
        }

        return $this->reset($user, $resetPasswordDTO);
    }

    /**
     * @param User $user
     * @param ResetPasswordDTO $resetPasswordDTO
     * @return string
     *
     * @throws PasswordResetFailedException
     */
    private function reset(User $user, ResetPasswordDTO $resetPasswordDTO): string
    {
        try {
            DB::beginTransaction();

            $user->update([
                'password' => Hash::make($resetPasswordDTO->getPassword()),
            ]);

            $this->passwordResetTokensCache->delete($resetPasswordDTO->getEmail());
            $user->tokens()->delete();

            DB::commit();
            return $user->createToken('auth_token')->plainTextToken;

        } catch (\Throwable $e) {
            DB::rollBack();
            throw new PasswordResetFailedException();
        }
    }
}
