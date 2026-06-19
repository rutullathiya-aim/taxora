<?php

declare(strict_types=1);

namespace App\Livewire\Projects;

use App\Enums\ChecklistStatus;
use App\Enums\CrudAction;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use HasProjectActions, HasProjectChecklistActions, WithPagination;

    public Project $project;

    public int $perPage = 10;

    private const VALID_TABS = [
        'checklists',
        'tasks',
    ];

    private const VALID_TASK_SORTS = [
        'created_at',
        'title',
        'due_at',
    ];

    private const TASK_PAGE_RESET_PROPERTIES = [
        'taskSearch',
        'taskStatusFilter',
        'taskPriorityFilter',
        'taskSortBy',
    ];

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $status = 'all';

    #[Url(except: '')]
    public string $taskSearch = '';

    #[Url(except: 'active')]
    public string $taskStatusFilter = 'active';

    #[Url(except: '')]
    public string $taskPriorityFilter = '';

    #[Url(except: 'due_at')]
    public string $taskSortBy = 'due_at';

    #[Url(except: 'checklists')]
    public string $currentTab = 'checklists';

    public function mount(Project $project): void
    {
        $this->perPage = auth()->user()?->getPreference('per_page', 10) ?? 10;
        $this->authorize('view', $project);
        $this->project = $project->load(['client', 'service', 'checklists', 'assignees']);

        if (! in_array($this->currentTab, self::VALID_TABS, true)) {
            $this->currentTab = 'checklists';
        }

        if (! in_array($this->taskSortBy, self::VALID_TASK_SORTS, true)) {
            $this->taskSortBy = 'due_at';
        }

        $validStatuses = [...array_column(ChecklistStatus::cases(), 'value'), 'all'];

        if (! in_array($this->status, $validStatuses, true)) {
            $this->status = 'all';
        }
    }

    public function updatedPerPage($value): void
    {
        $this->resetPage('tasksPage');
        auth()->user()?->setPreference('per_page', (int) $value);
    }

    public function setTaskStatusFilter(string $status): void
    {
        $this->taskStatusFilter = $status;
        $this->resetPage('tasksPage');
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, self::VALID_TABS, true)) {
            $this->currentTab = $tab;
        }
    }

    public function afterProjectAction(CrudAction $action, Project $project): void
    {
        if ($action === CrudAction::ForceDeleted && $this->project->id === $project->id) {
            $this->redirect(route('projects.index'), navigate: true);

            return;
        }

        if (in_array($action, [CrudAction::Deleted, CrudAction::Restored]) && $this->project->id === $project->id) {
            $this->project->refresh();
        }
    }

    public function afterProjectChecklistAction(string $action, $checklist = null): void
    {
        $this->dispatch('checklist-updated');
    }

    #[On('checklist-updated')]
    #[On('tasks.saved')]
    #[On('projects.saved')]
    public function refreshProject(): void
    {
        $this->project->refresh()->load([
            'client',
            'service',
            'checklists',
            'assignees',
        ]);
    }

    public function updated(string $property): void
    {
        if (in_array($property, self::TASK_PAGE_RESET_PROPERTIES, true)) {
            $this->resetPage('tasksPage');
        }
    }

    #[Computed]
    public function checklists(): Collection
    {
        return $this->checklistQuery()->get();
    }

    private function checklistQuery(): HasMany
    {
        $query = $this->project->checklists()
            ->with('documents.Document');

        $this->applyChecklistSearch($query);
        $this->applyChecklistStatusFilter($query);
        $this->applyChecklistSorting($query);

        return $query;
    }

    private function applyChecklistSearch(HasMany $query): void
    {
        $search = trim($this->search);

        if (blank($search)) {
            return;
        }

        $query->where('name', 'like', '%' . $search . '%');
    }

    private function applyChecklistStatusFilter(HasMany $query): void
    {
        if ($this->status === 'all') {
            return;
        }

        $query->where('status', $this->status);
    }

    private function applyChecklistSorting(HasMany $query): void
    {
        $query->orderBy('id');
    }

    #[Computed]
    public function tasks(): LengthAwarePaginator
    {
        return $this->taskQuery()
            ->paginate($this->perPage, ['*'], 'tasksPage');
    }

    private function taskQuery(): HasMany
    {
        $query = $this->project->tasks()->with(['assignees']);

        $user = auth()->user();

        if ($user->isStaff()) {
            $query->whereRelation('assignees', 'users.id', $user->id);
        }

        $this->applyTaskSearch($query);
        $this->applyTaskStatusFilter($query);
        $this->applyTaskPriorityFilter($query);
        $this->applyTaskSorting($query);

        return $query;
    }

    private function applyTaskSearch(HasMany $query): void
    {
        $search = trim($this->taskSearch);

        if (blank($search)) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%')
                ->orWhere('task_number', 'like', '%' . $search . '%');
        });
    }

    private function applyTaskStatusFilter(HasMany $query): void
    {
        if ($this->taskStatusFilter === 'active') {
            $query->whereIn('status', [TaskStatus::Todo, TaskStatus::InProgress]);

            return;
        }

        if (blank($this->taskStatusFilter)) {
            return;
        }

        $query->where('status', $this->taskStatusFilter);
    }

    private function applyTaskPriorityFilter(HasMany $query): void
    {
        if (blank($this->taskPriorityFilter)) {
            return;
        }

        $query->where('priority', $this->taskPriorityFilter);
    }

    private function applyTaskSorting(HasMany $query): void
    {
        match ($this->taskSortBy) {
            'created_at' => $query->orderBy('created_at', 'desc')->orderBy('id', 'desc'),
            'title' => $query->orderBy('title', 'asc')->orderBy('id'),
            default => $query->orderByRaw('due_at IS NULL, due_at ASC')->orderBy('id'),
        };
    }

    public function render(): View
    {
        return view('livewire.projects.show', [
            'taskStatuses' => TaskStatus::cases(),
            'taskPriorities' => TaskPriority::cases(),
        ])->layout('components.layouts.app');
    }

    #[Computed]
    public function progressStats(): array
    {
        $checklists = $this->checklists;
        $total = $checklists->count();
        $completed = $checklists->whereIn('status', [ChecklistStatus::Approved, ChecklistStatus::Submitted, ChecklistStatus::NotApplicable])->count();
        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $percentage,
        ];
    }
}
