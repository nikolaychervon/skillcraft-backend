<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Mentor\Cache\MentorCacheInterface;
use App\Domain\Mentor\Cache\TrackCacheInterface;
use App\Domain\Mentor\Events\MentorChanged;
use App\Domain\Mentor\Repositories\MentorRepositoryInterface;
use App\Domain\Mentor\Repositories\TrackRepositoryInterface;
use App\Infrastructure\Mentor\Cache\MentorCache;
use App\Infrastructure\Mentor\Cache\TrackCache;
use App\Infrastructure\Mentor\Listeners\InvalidateUserMentorsCache;
use App\Infrastructure\Mentor\Observers\MentorObserver;
use App\Infrastructure\Mentor\Repositories\Cached\CachedMentorRepository;
use App\Infrastructure\Mentor\Repositories\Cached\CachedTrackRepository;
use App\Infrastructure\Mentor\Repositories\MentorRepository;
use App\Infrastructure\Mentor\Repositories\TrackRepository;
use App\Models\Mentor;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class MentorServiceProvider extends ServiceProvider
{
    public $bindings = [
        TrackCacheInterface::class => TrackCache::class,
        MentorCacheInterface::class => MentorCache::class,
        MentorRepositoryInterface::class => CachedMentorRepository::class,
        TrackRepositoryInterface::class => CachedTrackRepository::class,
    ];

    public function register(): void
    {
        $this->app->when(CachedTrackRepository::class)
            ->needs(TrackRepositoryInterface::class)
            ->give(TrackRepository::class);

        $this->app->when(CachedMentorRepository::class)
            ->needs(MentorRepositoryInterface::class)
            ->give(MentorRepository::class);
    }

    public function boot(): void
    {
        Event::listen(MentorChanged::class, InvalidateUserMentorsCache::class);
        Mentor::observe(MentorObserver::class);
    }
}
