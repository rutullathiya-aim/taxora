<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;

class Trash extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function restoreProject(string $id): void
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        $project->restore();
        Flux::toast('Project restored successfully.', variant: 'success');
    }

    public function forceDeleteProject(string $id): void
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        $project->forceDelete();
        Flux::toast('Project permanently deleted.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.projects.trash', [
            'projects' => Project::onlyTrashed()
                ->with(['client', 'service'])
                ->when($this->search, function ($query) {
                    $query->where('project_name', 'like', '%'.$this->search.'%')
                        ->orWhereHas('client', function ($q) {
                            $q->where('client_name', 'like', '%'.$this->search.'%')
                                ->orWhere('company_name', 'like', '%'.$this->search.'%');
                        });
                })
                ->latest('deleted_at')
                ->paginate(10),
            'totalTrashed' => Project::onlyTrashed()->count(),
        ])->layout('components.layouts.app');
    }
}
