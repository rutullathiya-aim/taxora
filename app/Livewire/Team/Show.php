<?php

namespace App\Livewire\Team;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public User $teamMember;

    public string $currentTab = 'projects';

    // Filter models for projects table
    public string $projectSearch = '';

    public string $projectStatusFilter = '';

    public string $projectSortBy = 'created_at';

    public string $projectSortDirection = 'desc';

    // Filter models for tasks table
    public string $taskSearch = '';

    public string $taskStatusFilter = '';

    public string $taskPriorityFilter = '';

    public string $taskSortBy = 'created_at';

    public string $taskSortDirection = 'desc';

    public function mount(User $user): void
    {
        $this->authorize('view', $user);

        $this->teamMember = $user;
    }

    #[Computed]
    public function projects()
    {
        return $this->teamMember->projects()
            ->when($this->projectSearch, function ($query) {
                $query->where('name', 'like', '%' . $this->projectSearch . '%');
            })
            ->when($this->projectStatusFilter, function ($query) {
                $query->where('status', $this->projectStatusFilter);
            })
            ->orderBy($this->projectSortBy, $this->projectSortDirection)
            ->paginate(10, ['*'], 'projectsPage');
    }

    #[Computed]
    public function tasks()
    {
        return $this->teamMember->tasks()
            ->with(['assignees', 'client', 'project'])
            ->when($this->taskSearch, function ($query) {
                $query->where('title', 'like', '%' . $this->taskSearch . '%')
                    ->orWhere('task_number', 'like', '%' . $this->taskSearch . '%');
            })
            ->when($this->taskStatusFilter, function ($query) {
                $query->where('status', $this->taskStatusFilter);
            })
            ->when($this->taskPriorityFilter, function ($query) {
                $query->where('priority', $this->taskPriorityFilter);
            })
            ->orderBy($this->taskSortBy, $this->taskSortDirection)
            ->paginate(10, ['*'], 'tasksPage');
    }

    public function setTab(string $tab): void
    {
        $this->currentTab = $tab;
    }

    public function render(): View
    {
        return view('livewire.team.show', [
            'taskStatuses' => TaskStatus::cases(),
            'taskPriorities' => TaskPriority::cases(),
        ])->layout('components.layouts.app');
    }
}
