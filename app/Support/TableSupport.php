<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TableSupport
{
    public static function rowNumber(
        LengthAwarePaginator $paginator,
        int $index
    ): int {
        return ($paginator->firstItem() ?? 0) + $index;
    }
}
