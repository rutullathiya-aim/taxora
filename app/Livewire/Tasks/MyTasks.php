<?php

namespace App\Livewire\Tasks;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MyTasks extends Component
{
    use HasTaskActions, WithPagination;

    public string $search = '';

    public string $status = 'active';

    public string $priorityFilter = '';

    public string $sortBy = 'due_at';

    #[On('tasks.saved')]
    public function refreshTasks(): void {}

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'priorityFilter', 'sortBy'])) {
            $this->resetPage();
        }
    }

    protected function baseQuery(): Builder
    {
        return Task::query()
            ->where(fn ($q) => $q->whereNull('client_id')->orWhereHas('client'))
            ->where(fn ($q) => $q->whereNull('project_id')->orWhereHas('project'))
            ->whereHas('assignees', fn ($q) => $q->where('users.id', auth()->id()));
    }

    #[Computed]
    public function tasks(): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->with(['assignees', 'client', 'project'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('task_number', 'like', "%{$this->search}%")
                        ->orWhereHas('client', fn ($q) => $q->where('company_name', 'like', "%{$this->search}%"))
                        ->orWhereHas('project', fn ($q) => $q->where('project_name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status === 'active', fn ($query) => $query->whereIn('status', [TaskStatus::Todo, TaskStatus::InProgress]))
            ->when($this->status !== '' && $this->status !== 'active', fn ($query) => $query->where('status', $this->status))
            ->when($this->priorityFilter !== '', fn ($query) => $query->where('priority', $this->priorityFilter))
            ->when(true, function ($query) {
                match ($this->sortBy) {
                    'created_at' => $query->latest(),
                    'title' => $query->orderBy('title', 'asc'),
                    default => $query->orderBy('due_at', 'asc'),
                };
            })
            ->paginate(15);
    }

    #[Computed]
    public function stats(): array
    {
        $stats = $this->baseQuery()
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as todo', [TaskStatus::Todo->value])
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as in_progress', [TaskStatus::InProgress->value])
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as completed', [TaskStatus::Completed->value])
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as on_hold', [TaskStatus::OnHold->value])
            ->selectRaw('sum(case when due_at < ? and status not in (?, ?) then 1 else 0 end) as overdue', [
                now()->toDateTimeString(),
                TaskStatus::Completed->value,
                TaskStatus::Cancelled->value,
            ])
            ->first();

        $todo = (int) ($stats->todo ?? 0);
        $inProgress = (int) ($stats->in_progress ?? 0);
        $completed = (int) ($stats->completed ?? 0);
        $onHold = (int) ($stats->on_hold ?? 0);
        $overdue = (int) ($stats->overdue ?? 0);

        $total = $todo + $inProgress;

        return [
            'total' => $total,
            'todo' => $todo,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'on_hold' => $onHold,
            'overdue' => $overdue,
        ];
    }

    public function render(): View
    {
        return view('livewire.tasks.my-tasks', [
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ])->layout('components.layouts.app');
    }
}
