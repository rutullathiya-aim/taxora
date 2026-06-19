<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\Project\Created;
use App\Events\Project\Deleted;
use App\Events\Project\ForceDeleted;
use App\Events\Project\Restored;
use App\Events\Project\Updated;
use App\Stats\ProjectStats;
use Illuminate\Events\Dispatcher;

final readonly class InvalidateProjectStatsCache
{
    public function __construct(
        private ProjectStats $projectStats
    ) {}

    public function handle(Created|Updated|Deleted|Restored|ForceDeleted $event): void
    {
        $this->projectStats->forgetAll();
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
