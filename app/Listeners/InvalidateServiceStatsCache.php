<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\Service\Created;
use App\Events\Service\Deleted;
use App\Events\Service\ForceDeleted;
use App\Events\Service\Restored;
use App\Events\Service\Updated;
use App\Stats\ServiceStats;
use Illuminate\Events\Dispatcher;

final readonly class InvalidateServiceStatsCache
{
    public function __construct(
        private ServiceStats $serviceStats
    ) {}

    public function handle(Created|Updated|Deleted|Restored|ForceDeleted $event): void
    {
        $this->serviceStats->forgetAll();
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            [
                Created::class,
                Updated::class,
                Deleted::class,
                Restored::class,
                ForceDeleted::class,
            ],
            [self::class, 'handle']
        );
    }
}
