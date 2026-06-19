<?php

declare(strict_types=1);

namespace App\Filters;

abstract readonly class BaseFilters
{
    public function __construct(
        public ?string $search = null,
        public ?int $perPage = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
    ) {}
}
