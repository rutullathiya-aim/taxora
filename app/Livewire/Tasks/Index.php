<?php

namespace App\Livewire\Tasks;

use App\Enums\ListFilter;
use App\Enums\TaskPriority;
use App\Enums\TaskSort;
use App\Enums\TaskStatus;
use App\Livewire\Base\BaseTableComponent;
use App\Models\Task;
use App\Queries\TaskQueries;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class Index extends BaseTableComponent
{
    use HasTaskActions;

    protected function getPageResetProperties(): array
    {
        return [
            'search',
            'sortBy',
            'status',
            'priorityFilter',
        ];
    }

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'open')]
    public string $status = 'open';

    #[Url(except: 'all')]
    public string $priorityFilter = 'all';

    #[Url(except: TaskSort::Latest->value)]
    public string $sortBy = TaskSort::Latest->value;

    public function mount(): void
    {
        $this->perPage = auth()->user()?->getPreference('per_page', 15) ?? 15;
        $this->authorize('viewAny', Task::class);

        if (! self::isValidSort($this->sortBy)) {
            $this->sortBy = TaskSort::Latest->value;
        }

        if (! self::isValidStatus($this->status)) {
            $this->status = 'open';
        }

        if (! self::isValidPriority($this->priorityFilter)) {
            $this->priorityFilter = 'all';
        }
    }

    #[On('tasks.saved')]
    public function refreshTasks(): void
    {
        // Empty to trigger Livewire re-render
    }

    public function setStatusFilter(string $status): void
    {
        if (! self::isValidStatus($status)) {
            return;
        }

        $this->status = $status;
        $this->resetPage();
    }

    private function baseQuery(): Builder
    {
        return Task::query()
            ->withTrashed()
            ->visibleTo(auth()->user());
    }

    #[Computed]
    public function tasks(): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->select([
                'id',
                'task_number',
                'title',
                'status',
                'priority',
                'due_at',
                'client_id',
                'project_id',
                'created_by',
                'completed_at',
                'deleted_at',
                'created_at',
            ])
            ->with([
                'assignees:id,name',
                'client:id,client_name,company_name',
                'project:id,project_name',
            ])
            ->when($this->status === 'open', function ($query) {
                // 'open' means all non-deleted, non-completed/cancelled active tasks
                $query->whereIn('status', [
                    TaskStatus::Todo->value,
                    TaskStatus::InProgress->value,
                    TaskStatus::OnHold->value,
                ]);
            }, function ($query) {
                $query->filterStatus($this->status);
            })
            ->when($this->priorityFilter !== 'all', fn ($query) => $query->where('priority', $this->priorityFilter))
            ->search($this->search)
            ->sorted(TaskSort::tryFrom($this->sortBy) ?? TaskSort::Latest)
            ->paginate($this->perPage);
    }

    #[Computed]
    public function stats(): array
    {
        return TaskQueries::stats($this->baseQuery());
    }

    private static function isValidSort(string $sort): bool
    {
        return TaskSort::tryFrom($sort) !== null;
    }

    private static function validStatuses(): array
    {
        return [
            'open',
            ...array_keys(TaskStatus::options()),
            ...array_keys(ListFilter::options()),
        ];
    }

    private static function isValidStatus(string $status): bool
    {
        return in_array($status, self::validStatuses(), true);
    }

    private static function validPriorities(): array
    {
        return [
            'all',
            ...array_keys(TaskPriority::options()),
        ];
    }

    private static function isValidPriority(string $priority): bool
    {
        return in_array($priority, self::validPriorities(), true);
    }

    public function render(): View
    {
        return view('livewire.tasks.index', [
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ])->layout('components.layouts.app');
    }
}
