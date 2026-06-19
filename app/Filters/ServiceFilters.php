<?php

declare(strict_types=1);

namespace App\Filters;

use App\Enums\ServiceListStatus;
use App\Enums\ServiceSort;

final readonly class ServiceFilters extends BaseFilters
{
    public function __construct(
        ?string $search,
        ?int $perPage = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        public ServiceListStatus $status = ServiceListStatus::Active,
        public ServiceSort $sort = ServiceSort::Latest,
    ) {
        parent::__construct(
            $search,
            $perPage,
            $dateFrom,
            $dateTo,
        );
    }
}
