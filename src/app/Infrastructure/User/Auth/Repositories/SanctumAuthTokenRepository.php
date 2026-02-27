<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Auth\Repositories;

use App\Domain\User\Auth\Repositories\AuthTokenRepositoryInterface;
use App\Models\User as UserModel;

final class SanctumAuthTokenRepository implements AuthTokenRepositoryInterface
{
    public function createToken(int $userId, string $tokenName): string
    {
        $model = UserModel::query()->findOrFail($userId);

        return $model->createToken($tokenName)->plainTextToken;
    }

    public function deleteCurrentRequestToken(int $userId): void
    {
        $model = auth()->user();
        if ($model !== null && $model->id === $userId) {
            $model->currentAccessToken()->delete();
        }
    }

    public function deleteAllTokens(int $userId): void
    {
        $model = UserModel::query()->find($userId);
        $model?->tokens()->delete();
    }
}
