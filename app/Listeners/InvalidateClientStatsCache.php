<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\Client\Created;
use App\Events\Client\Deleted;
use App\Events\Client\ForceDeleted;
use App\Events\Client\Restored;
use App\Events\Client\Updated;
use App\Stats\ClientStats;
use Illuminate\Events\Dispatcher;

final readonly class InvalidateClientStatsCache
{
    public function __construct(
        private ClientStats $clientStats
    ) {}

    public function handle(Created|Updated|Deleted|Restored|ForceDeleted $event): void
    {
        $this->clientStats->forgetAll();
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
