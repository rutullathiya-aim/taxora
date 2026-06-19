<?php

namespace App\Livewire\Tasks;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    use HasTaskInteractions;

    public Task $task;

    public function mount(Task $task): void
    {
        $this->authorize('view', $task);

        $this->task = $task->load([
            'assignees',
            'client',
            'project',
            'createdBy',
            'completedBy',
            'comments.user',
            'activities' => fn ($q) => $q->with('user')->latest(),
        ]);
    }

    #[Computed]
    public function timeline()
    {
        return $this->task->activities->map(fn ($activity) => [
            'type' => 'activity',
            'model' => $activity,
            'date' => $activity->created_at,
        ]);
    }

    public function render(): View
    {
        return view('livewire.tasks.show', [
            'statuses' => TaskStatus::cases(),
        ])->layout('components.layouts.app');
    }
}
