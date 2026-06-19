<?php

declare(strict_types=1);

namespace App\Stats;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

final readonly class TeamStats
{
    private const CACHE_PREFIX = 'team_stats:';

    /**
     * @return array<string, int>
     */
    public function cards(): array
    {
        $ttl = config('taxora.cache.stats_ttl', 300);

        return Cache::remember(
            $this->cacheKey(),
            $ttl,
            function () {
                $statusActive = UserStatus::Active->value;
                $statusInactive = UserStatus::Inactive->value;
                $statusPending = UserStatus::Pending->value;

                $stats = User::query()
                    ->withTrashed()
                    ->toBase()
                    ->selectRaw(
                        '
                        COUNT(CASE WHEN deleted_at IS NULL THEN 1 END) as total,
                        COUNT(CASE WHEN is_active = ? AND email_verified_at IS NOT NULL AND deleted_at IS NULL THEN 1 END) as active,
                        COUNT(CASE WHEN is_active = ? AND email_verified_at IS NOT NULL AND deleted_at IS NULL THEN 1 END) as inactive,
                        COUNT(CASE WHEN email_verified_at IS NULL AND deleted_at IS NULL THEN 1 END) as pending,
                        COUNT(CASE WHEN deleted_at IS NOT NULL THEN 1 END) as deleted
                    ',
                        [1, 0]
                    )
                    ->first();

                return [
                    'total' => (int) ($stats->total ?? 0),
                    'active' => (int) ($stats->active ?? 0),
                    'inactive' => (int) ($stats->inactive ?? 0),
                    'pending' => (int) ($stats->pending ?? 0),
                    'deleted' => (int) ($stats->deleted ?? 0),
                ];
            }
        );
    }

    public function forget(): void
    {
        Cache::forget($this->cacheKey());
    }

    public function forgetAll(): void
    {
        $this->forget();
    }

    private function cacheKey(): string
    {
        return self::CACHE_PREFIX . 'global';
    }
}
