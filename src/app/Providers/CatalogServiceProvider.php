<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Catalog\Cache\CatalogCacheInterface;
use App\Domain\Catalog\Repositories\ProgrammingLanguageRepositoryInterface;
use App\Domain\Catalog\Repositories\SpecializationRepositoryInterface;
use App\Infrastructure\Catalog\Cache\CatalogCache;
use App\Infrastructure\Catalog\Repositories\Cached\CachedProgrammingLanguageRepository;
use App\Infrastructure\Catalog\Repositories\Cached\CachedSpecializationRepository;
use App\Infrastructure\Catalog\Repositories\ProgrammingLanguageRepository;
use App\Infrastructure\Catalog\Repositories\SpecializationRepository;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider
{
    public $bindings = [
        CatalogCacheInterface::class => CatalogCache::class,
        SpecializationRepositoryInterface::class => CachedSpecializationRepository::class,
        ProgrammingLanguageRepositoryInterface::class => CachedProgrammingLanguageRepository::class,
    ];

    public function register(): void
    {
        $this->app->when(CachedSpecializationRepository::class)
            ->needs(SpecializationRepositoryInterface::class)
            ->give(SpecializationRepository::class);

        $this->app->when(CachedProgrammingLanguageRepository::class)
            ->needs(ProgrammingLanguageRepositoryInterface::class)
            ->give(ProgrammingLanguageRepository::class);
    }
}
