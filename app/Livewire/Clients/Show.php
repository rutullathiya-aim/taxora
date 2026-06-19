<?php

namespace App\Livewire\Clients;

use App\Enums\ChecklistStatus;
use App\Enums\CrudAction;
use App\Enums\ListFilter;
use App\Enums\ProjectSort;
use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskSort;
use App\Enums\TaskStatus;
use App\Livewire\Projects\HasProjectActions;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Queries\ProjectQueries;
use App\Queries\TaskQueries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use HasClientActions, HasProjectActions, WithPagination;

    private const VALID_TABS = [
        'projects',
        'tasks',
        'documents',
    ];

    public int $perPage = 10;

    private const PROJECT_RESET_PROPERTIES = [
        'projectSearch',
        'projectStatusFilter',
        'projectSortBy',
    ];

    private const TASK_RESET_PROPERTIES = [
        'taskSearch',
        'taskStatusFilter',
        'taskPriorityFilter',
        'taskSortBy',
    ];

    public Client $client;

    #[Url(keep: true)]
    public string $currentTab = 'projects';

    #[Url(except: '')]
    public string $projectSearch = '';

    #[Url(as: 'projectStatus', except: 'active')]
    public string $projectStatusFilter = 'active';

    #[Url(except: 'latest')]
    public string $projectSortBy = 'latest';

    #[Url(except: '')]
    public string $taskSearch = '';

    #[Url(as: 'taskStatus', except: 'open')]
    public string $taskStatusFilter = 'open';

    #[Url(as: 'taskPriority', except: 'all')]
    public string $taskPriorityFilter = 'all';

    #[Url(except: 'latest')]
    public string $taskSortBy = 'latest';

    public function mount(Client $client): void
    {
        $this->perPage = auth()->user()?->getPreference('per_page', 10) ?? 10;
        $this->authorize('view', $client);
        $this->client = $client;

        if (! in_array($this->currentTab, self::VALID_TABS, true)) {
            $this->currentTab = 'projects';
        }

        if (! self::isValidProjectSort($this->projectSortBy)) {
            $this->projectSortBy = ProjectSort::Latest->value;
        }

        if (! self::isValidProjectStatus($this->projectStatusFilter)) {
            $this->projectStatusFilter = ProjectStatus::Active->value;
        }

        if (! self::isValidTaskSort($this->taskSortBy)) {
            $this->taskSortBy = TaskSort::Latest->value;
        }

        if (! self::isValidTaskStatus($this->taskStatusFilter)) {
            $this->taskStatusFilter = 'open';
        }

        if (! self::isValidTaskPriority($this->taskPriorityFilter)) {
            $this->taskPriorityFilter = 'all';
        }
    }

    private static function isValidProjectSort(string $sort): bool
    {
        return ProjectSort::tryFrom($sort) !== null;
    }

    private static function isValidProjectStatus(string $status): bool
    {
        $allowed = [
            ...array_keys(ProjectStatus::options()),
            ...array_keys(ListFilter::options()),
        ];

        return in_array($status, $allowed, true);
    }

    private static function isValidTaskSort(string $sort): bool
    {
        return TaskSort::tryFrom($sort) !== null;
    }

    private static function isValidTaskStatus(string $status): bool
    {
        $allowed = [
            ...array_keys(TaskStatus::options()),
            ...array_keys(ListFilter::options()),
            'open',
        ];

        return in_array($status, $allowed, true);
    }

    private static function isValidTaskPriority(string $priority): bool
    {
        $allowed = [
            ...array_keys(TaskPriority::options()),
            'all',
        ];

        return in_array($priority, $allowed, true);
    }

    public function updatedPerPage($value): void
    {
        $this->resetPage('projectsPage');
        $this->resetPage('tasksPage');
        auth()->user()?->setPreference('per_page', (int) $value);
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, self::VALID_TABS, true)) {
            $this->currentTab = $tab;
            $this->updatedCurrentTab();
        }
    }

    public function updatedCurrentTab(): void
    {
        $this->resetInactiveTabFilters();
    }

    private function resetInactiveTabFilters(): void
    {
        if ($this->currentTab === 'projects') {
            $this->resetTaskFilters();

            return;
        }

        if ($this->currentTab === 'tasks') {
            $this->resetProjectFilters();

            return;
        }

        $this->resetProjectFilters();
        $this->resetTaskFilters();
    }

    private function resetProjectFilters(): void
    {
        $this->reset(self::PROJECT_RESET_PROPERTIES);
        $this->resetPage('projectsPage');
    }

    private function resetTaskFilters(): void
    {
        $this->reset(self::TASK_RESET_PROPERTIES);
        $this->resetPage('tasksPage');
    }

    public function afterClientAction(CrudAction $action, Client $client): void
    {
        if ($action === CrudAction::ForceDeleted && $this->client->id === $client->id) {
            $this->redirect(route('clients.index'), navigate: true);

            return;
        }

        if (in_array($action, [CrudAction::Deleted, CrudAction::Restored]) && $this->client->id === $client->id) {
            $this->client->refresh();
        }
    }

    #[On('clients.saved')]
    public function refreshClient(): void
    {
        $this->client->refresh();
        $this->dispatch('update-heading', $this->client->client_name);
    }

    #[On('tasks.saved')]
    #[On('projects.saved')]
    public function refreshLists(): void
    {
        // Re-render component
    }

    public function updated(string $property): void
    {
        if (in_array($property, self::PROJECT_RESET_PROPERTIES, true)) {
            $this->resetPage('projectsPage');
        }

        if (in_array($property, self::TASK_RESET_PROPERTIES, true)) {
            $this->resetPage('tasksPage');
        }
    }

    public function resetFilters(): void
    {
        if ($this->currentTab === 'projects') {
            $this->resetProjectFilters();
        } elseif ($this->currentTab === 'tasks') {
            $this->resetTaskFilters();
        }
    }

    protected function projectQuery(): Builder
    {
        $user = auth()->user();

        return Project::query()
            ->where('client_id', $this->client->id)
            ->with([
                'service:id,name',
                'assignees:id,name',
            ])
            ->withCount([
                'checklists as total_checklists',
                'checklists as completed_checklists' => fn ($checklistQuery) => $checklistQuery->whereIn('status', ChecklistStatus::completionStatuses()),
            ])
            ->visibleTo($user);
    }

    #[Computed]
    public function projects(): LengthAwarePaginator
    {
        $query = $this->projectQuery();

        $query->search($this->projectSearch)
            ->filterStatus($this->projectStatusFilter)
            ->sorted(ProjectSort::tryFrom($this->projectSortBy) ?? ProjectSort::Latest);

        return $query->paginate($this->perPage, ['*'], 'projectsPage');
    }

    #[Computed]
    public function projectStats(): array
    {
        return ProjectQueries::stats(
            $this->client->projects()->withTrashed()->visibleTo(auth()->user())
        );
    }

    #[Computed]
    public function taskStats(): array
    {
        return TaskQueries::stats($this->taskQuery());
    }

    protected function taskQuery(): Builder
    {
        $user = auth()->user();

        return Task::query()
            ->where('client_id', $this->client->id)
            ->with([
                'assignees:id,name',
                'project:id,project_name',
            ])
            ->where(fn ($q) => $q->whereNull('project_id')->orWhereHas('project'))
            ->visibleTo($user);
    }

    #[Computed]
    public function tasks(): LengthAwarePaginator
    {
        $query = $this->taskQuery();

        $query->search($this->taskSearch)
            ->filterStatus($this->taskStatusFilter)
            ->when($this->taskPriorityFilter !== 'all', fn ($q) => $q->where('priority', $this->taskPriorityFilter))
            ->sorted(TaskSort::tryFrom($this->taskSortBy) ?? TaskSort::Latest);

        return $query->paginate($this->perPage, ['*'], 'tasksPage');
    }

    public function render(): View
    {
        return view('livewire.clients.show', [
            'taskStatuses' => TaskStatus::cases(),
            'taskPriorities' => TaskPriority::cases(),
        ])->layout('components.layouts.app');
    }
}
