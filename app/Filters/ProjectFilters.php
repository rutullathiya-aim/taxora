<?php

declare(strict_types=1);

namespace App\Filters;

use App\Enums\ProjectListStatus;
use App\Enums\ProjectSort;

final readonly class ProjectFilters extends BaseFilters
{
    public function __construct(
        ?string $search,
        ?int $perPage = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        public ProjectListStatus $status = ProjectListStatus::Active,
        public ProjectSort $sort = ProjectSort::Latest,
    ) {
        parent::__construct(
            $search,
            $perPage,
            $dateFrom,
            $dateTo,
        );
    }
}
