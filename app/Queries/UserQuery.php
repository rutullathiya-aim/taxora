<?php

declare(strict_types=1);

namespace App\Queries;

use App\Filters\UserFilters;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class UserQuery
{
    public function query(UserFilters $filters): Builder
    {
        $query = User::query()
            ->select(['id', 'name', 'email', 'phone', 'role', 'is_active', 'email_verified_at', 'last_login_at', 'created_at', 'deleted_at'])
            ->filterStatus($filters->status)
            ->filterRole($filters->role)
            ->search($filters->search);

        $filters->sort->apply($query);

        return $query;
    }
}
