<?php

declare(strict_types=1);

namespace App\Stats;

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

final readonly class ClientStats
{
    private const CACHE_PREFIX = 'client_stats:';

    private const VERSION_KEY = 'client_stats_version';

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
                $statusActive = ClientStatus::Active->value;
                $statusInactive = ClientStatus::Inactive->value;

                $stats = Client::query()
                    ->withTrashed()
                    ->visibleTo($user)
                    ->toBase()
                    ->selectRaw(
                        '
                        COUNT(CASE WHEN deleted_at IS NULL THEN 1 END) as total,
                        COUNT(CASE WHEN status = ? AND deleted_at IS NULL THEN 1 END) as active,
                        COUNT(CASE WHEN status = ? AND deleted_at IS NULL THEN 1 END) as inactive,
                        COUNT(CASE WHEN deleted_at IS NOT NULL THEN 1 END) as deleted
                    ',
                        [$statusActive, $statusInactive]
                    )
                    ->first();

                return [
                    'total' => (int) ($stats->total ?? 0),
                    'active' => (int) ($stats->active ?? 0),
                    'inactive' => (int) ($stats->inactive ?? 0),
                    'deleted' => (int) ($stats->deleted ?? 0),
                ];
            }
        );
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
