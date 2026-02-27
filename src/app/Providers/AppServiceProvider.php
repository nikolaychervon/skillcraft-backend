<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\User\Auth\Cache\PasswordResetTokensCacheInterface;
use App\Domain\User\Auth\Repositories\AuthTokenRepositoryInterface;
use App\Domain\User\Auth\Services\HashServiceInterface;
use App\Domain\User\Auth\Services\NotificationServiceInterface;
use App\Domain\User\Auth\Services\ResetTokenGeneratorInterface;
use App\Domain\User\Auth\Services\TokenServiceInterface;
use App\Domain\User\Auth\Services\TransactionServiceInterface;
use App\Domain\User\Profile\Services\ProfileNotificationServiceInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\User\Auth\Cache\PasswordResetTokensCache;
use App\Infrastructure\User\Auth\Repositories\SanctumAuthTokenRepository;
use App\Infrastructure\User\Auth\Services\HashService;
use App\Infrastructure\User\Auth\Services\NotificationService;
use App\Infrastructure\User\Auth\Services\ResetTokenGenerator;
use App\Infrastructure\User\Auth\Services\TokenService;
use App\Infrastructure\User\Auth\Services\TransactionService;
use App\Infrastructure\User\Profile\Services\ProfileNotificationService;
use App\Infrastructure\User\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        // Репозиторий пользователей
        UserRepositoryInterface::class => UserRepository::class,
        AuthTokenRepositoryInterface::class => SanctumAuthTokenRepository::class,

        // Кэш
        PasswordResetTokensCacheInterface::class => PasswordResetTokensCache::class,

        // Сервисы аутентификации
        HashServiceInterface::class => HashService::class,
        TokenServiceInterface::class => TokenService::class,
        NotificationServiceInterface::class => NotificationService::class,
        ResetTokenGeneratorInterface::class => ResetTokenGenerator::class,
        TransactionServiceInterface::class => TransactionService::class,

        // Сервисы профиля
        ProfileNotificationServiceInterface::class => ProfileNotificationService::class,
    ];
}
