<?php

declare(strict_types=1);

namespace App\Stats;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

final readonly class ProjectStats
{
    private const CACHE_PREFIX = 'project_stats:';

    private const VERSION_KEY = 'project_stats_version';

    /**
     * @return array<string, int>
     */
    public function cards(User $user): array
    {
        $ttl = config('taxora.cache.stats_ttl', 300);

        return Cache::remember(
            $this->cacheKey($user->id),
            $ttl,
            function () use ($user) {
                $statusActive = ProjectStatus::Active->value;
                $statusCompleted = ProjectStatus::Completed->value;
                $statusOnHold = ProjectStatus::OnHold->value;
                $statusCancelled = ProjectStatus::Cancelled->value;

                $stats = Project::query()
                    ->withTrashed()
                    ->visibleTo($user)
                    ->toBase()
                    ->selectRaw(
                        '
                        COUNT(CASE WHEN deleted_at IS NULL THEN 1 END) as total,
                        COUNT(CASE WHEN status = ? AND deleted_at IS NULL THEN 1 END) as active,
                        COUNT(CASE WHEN status = ? AND deleted_at IS NULL THEN 1 END) as completed,
                        COUNT(CASE WHEN status = ? AND deleted_at IS NULL THEN 1 END) as on_hold,
                        COUNT(CASE WHEN status = ? AND deleted_at IS NULL THEN 1 END) as cancelled,
                        COUNT(CASE WHEN deleted_at IS NOT NULL THEN 1 END) as deleted
                    ',
                        [$statusActive, $statusCompleted, $statusOnHold, $statusCancelled]
                    )
                    ->first();

                return [
                    'total' => (int) ($stats->total ?? 0),
                    'active' => (int) ($stats->active ?? 0),
                    'completed' => (int) ($stats->completed ?? 0),
                    'on_hold' => (int) ($stats->on_hold ?? 0),
                    'cancelled' => (int) ($stats->cancelled ?? 0),
                    'deleted' => (int) ($stats->deleted ?? 0),
                ];
            });
    }

    public function forget(string $userId): void
    {
        Cache::forget($this->cacheKey($userId));
    }

    public function forgetAll(): void
    {
        if (! Cache::add(self::VERSION_KEY, 1)) {
            Cache::increment(self::VERSION_KEY);
        }
    }

    private function cacheKey(string $userId): string
    {
        $version = (int) Cache::get(self::VERSION_KEY, 0);

        return self::CACHE_PREFIX . $userId . ':v' . $version;
    }
}
