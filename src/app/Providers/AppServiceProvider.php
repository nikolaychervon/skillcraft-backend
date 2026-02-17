<?php

namespace App\Providers;

use App\Domain\Auth\Cache\PasswordResetTokensCacheInterface;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Infrastructure\Auth\Cache\PasswordResetTokensCache;
use App\Infrastructure\Auth\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        // Repositories
        UserRepositoryInterface::class => UserRepository::class,

        // Cache
        PasswordResetTokensCacheInterface::class => PasswordResetTokensCache::class,
    ];
}
