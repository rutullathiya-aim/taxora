<?php

declare(strict_types=1);

namespace App\Queries;

use App\Filters\ClientFilters;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class ClientQuery
{
    public function query(User $user, ClientFilters $filters): Builder
    {
        $query = Client::query()
            ->select(['id', 'client_name', 'company_name', 'email', 'phone', 'status', 'created_at', 'deleted_at'])
            ->visibleTo($user)
            ->filterStatus($filters->status)
            ->search($filters->search);

        $filters->sort->apply($query);

        return $query;
    }
}
