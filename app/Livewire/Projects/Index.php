<?php

namespace App\Livewire\Projects;

use App\Models\Client;
use App\Models\Project;
use App\Models\Service;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public string $sortBy = 'latest';

    #[On('project-saved')]
    public function refreshProjects(): void
    {
        // Re-render
    }

    public function deleteProject(string $id): void
    {
        $project = Project::findOrFail($id);
        $project->delete();
        Flux::toast('Project deleted successfully.', variant: 'success');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSortBy(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.projects.index', [
            'projects' => Project::query()
                ->with(['client', 'service', 'projectChecklists'])
                ->when($this->search, function ($query) {
                    $query->where('project_name', 'like', '%'.$this->search.'%');
                })
                ->when($this->statusFilter !== 'all', function ($query) {
                    $query->where('status', $this->statusFilter);
                })
                ->when($this->sortBy === 'latest', fn ($query) => $query->latest())
                ->when($this->sortBy === 'oldest', fn ($query) => $query->oldest())
                ->when($this->sortBy === 'a_to_z', fn ($query) => $query->orderBy('project_name', 'asc'))
                ->when($this->sortBy === 'z_to_a', fn ($query) => $query->orderBy('project_name', 'desc'))
                ->paginate(10),
            'clients' => Client::orderBy('client_name')->get(),
            'services' => Service::where('status', 'active')->orderBy('name')->get(),
            'totalProjects' => Project::count(),
            'inProgressProjects' => Project::where('status', 'in_progress')->count(),
            'completedProjects' => Project::where('status', 'completed')->count(),
            'onHoldProjects' => Project::where('status', 'on_hold')->count(),
        ])->layout('components.layouts.app');
    }
}
