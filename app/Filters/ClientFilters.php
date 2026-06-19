<?php

declare(strict_types=1);

namespace App\Filters;

use App\Enums\ClientListStatus;
use App\Enums\ClientSort;

final readonly class ClientFilters extends BaseFilters
{
    public function __construct(
        ?string $search,
        ?int $perPage = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        public ClientListStatus $status = ClientListStatus::Active,
        public ClientSort $sort = ClientSort::Latest,
    ) {
        parent::__construct(
            $search,
            $perPage,
            $dateFrom,
            $dateTo,
        );
    }
}
