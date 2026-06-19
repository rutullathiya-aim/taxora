<?php

namespace App\Livewire\Tasks;

use App\Enums\TaskActivityType;
use App\Models\Task;
use App\Models\TaskActivity;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;

trait HasTaskActions
{
    use AuthorizesRequests;

    #[On('delete-task')]
    public function deleteTask(string $id): void
    {
        $task = Task::findOrFail($id);
        $this->authorize('delete', $task);
        $task->delete();

        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => TaskActivityType::Updated->value,
            'description' => 'Task deleted',
        ]);

        Flux::toast('Task moved to trash.', variant: 'success');
        $this->runAfterTaskAction('delete', $task);
    }

    #[On('restore-task')]
    public function restoreTask(string $id): void
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $task);
        $task->restore();

        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => TaskActivityType::Updated->value,
            'description' => 'Task restored',
        ]);

        Flux::toast('Task restored successfully.', variant: 'success');
        $this->runAfterTaskAction('restore', $task);
    }

    #[On('force-delete-task')]
    public function forceDeleteTask(string $id): void
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $task);
        $task->forceDelete();
        Flux::toast('Task permanently deleted.', variant: 'success');
        $this->runAfterTaskAction('forceDelete', $task);
    }

    private function runAfterTaskAction(string $action, Task $task): void
    {
        if (method_exists($this, 'afterTaskAction')) {
            $this->afterTaskAction($action, $task);
        }
    }
}
