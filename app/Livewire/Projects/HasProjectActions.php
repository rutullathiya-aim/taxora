<?php

declare(strict_types=1);

namespace App\Livewire\Projects;

use App\Enums\CrudAction;
use App\Enums\ResourceType;
use App\Models\Project;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;

trait HasProjectActions
{
    use AuthorizesRequests;

    #[On('delete-project')]
    public function deleteProject(string $id): void
    {
        $project = Project::query()->findOrFail($id);
        $this->authorize('delete', $project);
        $this->projectManager->delete($project);
        $this->invokeAfterProjectAction(CrudAction::Deleted, $project);
        Flux::toast(CrudAction::Deleted->message(ResourceType::Project), variant: 'success');
    }

    #[On('restore-project')]
    public function restoreProject(string $id): void
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $project);
        $this->projectManager->restore($project);
        $this->invokeAfterProjectAction(CrudAction::Restored, $project);
        Flux::toast(CrudAction::Restored->message(ResourceType::Project), variant: 'success');
    }

    #[On('force-delete-project')]
    public function forceDeleteProject(string $id): void
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $project);
        $this->projectManager->forceDelete($project);
        $this->invokeAfterProjectAction(CrudAction::ForceDeleted, $project);
        Flux::toast(CrudAction::ForceDeleted->message(ResourceType::Project), variant: 'success');
    }

    private function invokeAfterProjectAction(CrudAction $action, Project $project): void
    {
        if (method_exists($this, 'afterProjectAction')) {
            $this->afterProjectAction($action, $project);
        }
    }
}
