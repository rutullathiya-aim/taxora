<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Events\Project\Created;
use App\Events\Project\Deleted;
use App\Events\Project\ForceDeleted;
use App\Events\Project\Restored;
use App\Events\Project\Updated;
use App\Models\Project;
use App\Support\UserContext;
use Illuminate\Support\Facades\DB;

final readonly class ProjectManager
{
    public function __construct(
        private ProjectChecklistSeeder $checklistSeeder,
        private UserContext $userContext,
    ) {}

    public function create(array $data, array $assigneeIds = []): Project
    {
        $project = DB::transaction(function () use ($data, $assigneeIds) {
            $data['created_by'] ??= $this->userContext->getId();

            $project = Project::create($data);
            $project->assignees()->sync($assigneeIds);

            $this->checklistSeeder->seed($project);

            return $project;
        });

        Created::dispatch($project, $this->userContext->get());

        return $project;
    }

    public function update(Project $project, array $data, array $assigneeIds = []): Project
    {
        $project = DB::transaction(function () use ($project, $data, $assigneeIds) {
            $project->update($data);
            $project->assignees()->sync($assigneeIds);

            return $project->fresh();
        });

        Updated::dispatch($project, $this->userContext->get());

        return $project;
    }

    public function delete(Project $project): void
    {
        DB::transaction(function () use ($project) {
            $project->update(['status' => ProjectStatus::Cancelled->value]);
            $project->delete();
        });

        Deleted::dispatch($project, $this->userContext->get());
    }

    public function restore(Project $project): void
    {
        $project->restore();

        Restored::dispatch($project, $this->userContext->get());
    }

    public function forceDelete(Project $project): void
    {
        $project->forceDelete();

        ForceDeleted::dispatch($project, $this->userContext->get());
    }
}
