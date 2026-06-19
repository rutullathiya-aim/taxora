<?php

declare(strict_types=1);

namespace App\Filters;

use App\Enums\UserListRole;
use App\Enums\UserListStatus;
use App\Enums\UserSort;

final readonly class UserFilters extends BaseFilters
{
    public function __construct(
        ?string $search,
        ?int $perPage = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        public UserListStatus $status = UserListStatus::Active,
        public UserSort $sort = UserSort::Latest,
        public UserListRole $role = UserListRole::All,
    ) {
        parent::__construct(
            $search,
            $perPage,
            $dateFrom,
            $dateTo,
        );
    }
}
