<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Catalog\Cache\CatalogCacheInterface;
use App\Domain\Catalog\Repositories\SpecializationRepositoryInterface;
use App\Domain\User\Auth\Cache\PasswordResetTokensCacheInterface;
use App\Infrastructure\Catalog\Cache\CatalogCache;
use App\Infrastructure\Catalog\Repositories\CachedSpecializationRepository;
use App\Domain\User\Auth\Services\HashServiceInterface;
use App\Domain\User\Auth\Services\NotificationServiceInterface;
use App\Domain\User\Auth\Services\TokenGeneratorInterface;
use App\Domain\User\Auth\Services\TokenServiceInterface;
use App\Domain\User\Auth\Services\TransactionServiceInterface;
use App\Domain\User\Profile\Services\ProfileNotificationServiceInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\User\Auth\Cache\PasswordResetTokensCache;
use App\Infrastructure\User\Auth\Services\HashService;
use App\Infrastructure\User\Auth\Services\NotificationService;
use App\Infrastructure\User\Auth\Services\TokenGenerator;
use App\Infrastructure\User\Auth\Services\TokenService;
use App\Infrastructure\User\Auth\Services\TransactionService;
use App\Infrastructure\User\Profile\Services\ProfileNotificationService;
use App\Infrastructure\User\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        // Catalog
        CatalogCacheInterface::class => CatalogCache::class,
        SpecializationRepositoryInterface::class => CachedSpecializationRepository::class,

        // User aggregate repository
        UserRepositoryInterface::class => UserRepository::class,

        // Cache
        PasswordResetTokensCacheInterface::class => PasswordResetTokensCache::class,

        // Auth services
        HashServiceInterface::class => HashService::class,
        TokenServiceInterface::class => TokenService::class,
        NotificationServiceInterface::class => NotificationService::class,
        TokenGeneratorInterface::class => TokenGenerator::class,
        TransactionServiceInterface::class => TransactionService::class,

        // Profile services
        ProfileNotificationServiceInterface::class => ProfileNotificationService::class,
    ];
}
