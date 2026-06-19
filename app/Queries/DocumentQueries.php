<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class DocumentQueries
{
    public static function stats(Builder|Relation $query): array
    {
        $stats = $query->toBase()->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) as deleted
        ')->first();

        return [
            'total' => (int) $stats?->total,
            'active' => (int) $stats?->active,
            'deleted' => (int) $stats?->deleted,
        ];
    }
}
