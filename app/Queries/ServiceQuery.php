<?php

declare(strict_types=1);

namespace App\Queries;

use App\Filters\ServiceFilters;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class ServiceQuery
{
    public function query(User $user, ServiceFilters $filters): Builder
    {
        $query = Service::query()
            ->select(['id', 'name', 'slug', 'description', 'status', 'created_at', 'deleted_at'])
            ->visibleTo($user)
            ->filterStatus($filters->status)
            ->search($filters->search);

        $filters->sort->apply($query);

        return $query;
    }
}
