<?php

namespace App\Queries;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskQueries
{
    /**
     * Get aggregate statistics for a given task query.
     */
    public static function stats(Builder|HasMany $query): array
    {
        $stats = $query->toBase()
            ->selectRaw('count(case when status = ? and deleted_at is null then 1 end) as todo', [TaskStatus::Todo->value])
            ->selectRaw('count(case when status = ? and deleted_at is null then 1 end) as in_progress', [TaskStatus::InProgress->value])
            ->selectRaw('count(case when status = ? and deleted_at is null then 1 end) as completed', [TaskStatus::Completed->value])
            ->selectRaw('count(case when status = ? and deleted_at is null then 1 end) as on_hold', [TaskStatus::OnHold->value])
            ->selectRaw('count(case when status = ? and deleted_at is null then 1 end) as cancelled', [TaskStatus::Cancelled->value])
            ->selectRaw('count(case when due_at is not null and due_at < ? and status not in (?, ?) and deleted_at is null then 1 end) as overdue', [
                now()->toDateTimeString(),
                TaskStatus::Completed->value,
                TaskStatus::Cancelled->value,
            ])
            ->selectRaw('count(case when deleted_at is not null then 1 end) as deleted')
            ->first();

        $todo = (int) ($stats->todo ?? 0);
        $inProgress = (int) ($stats->in_progress ?? 0);
        $completed = (int) ($stats->completed ?? 0);
        $onHold = (int) ($stats->on_hold ?? 0);
        $cancelled = (int) ($stats->cancelled ?? 0);
        $overdue = (int) ($stats->overdue ?? 0);
        $deleted = (int) ($stats->deleted ?? 0);

        $total = $todo + $inProgress + $onHold; // Open tasks.

        return [
            'total' => $total,
            'todo' => $todo,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'on_hold' => $onHold,
            'cancelled' => $cancelled,
            'overdue' => $overdue,
            'deleted' => $deleted,
        ];
    }
}
