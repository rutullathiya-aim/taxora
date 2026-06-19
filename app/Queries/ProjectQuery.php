<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ChecklistStatus;
use App\Filters\ProjectFilters;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class ProjectQuery
{
    public function query(User $user, ProjectFilters $filters): Builder
    {
        $query = Project::query()
            ->select(['id', 'project_code', 'client_id', 'service_id', 'project_name', 'status', 'due_date', 'created_at', 'deleted_at'])
            ->with(['client:id,client_name', 'service:id,name', 'assignees:id,name'])
            ->withCount([
                'checklists as total_checklists',
                'checklists as completed_checklists' => fn ($checklistQuery) => $checklistQuery->whereIn('status', ChecklistStatus::completionStatuses()),
            ])
            ->visibleTo($user)
            ->filterStatus($filters->status)
            ->search($filters->search);

        $filters->sort->apply($query);

        return $query;
    }
}
