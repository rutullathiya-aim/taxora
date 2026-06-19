<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\Team\Created;
use App\Events\Team\Deleted;
use App\Events\Team\ForceDeleted;
use App\Events\Team\Restored;
use App\Events\Team\Updated;
use App\Stats\TeamStats;
use Illuminate\Events\Dispatcher;

final readonly class InvalidateTeamStatsCache
{
    public function __construct(
        private TeamStats $teamStats
    ) {}

    public function handle(Created|Updated|Deleted|Restored|ForceDeleted $event): void
    {
        $this->teamStats->forgetAll();
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
