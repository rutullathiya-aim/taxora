<?php

namespace App\Livewire\Tasks;

use App\Enums\ClientStatus;
use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskManager;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class TaskForm extends Component
{
    public bool $showModal = false;

    public ?Task $task = null;

    public string $title = '';

    public ?string $description = null;

    public string $status = TaskStatus::Todo->value;

    public string $priority = TaskPriority::Medium->value;

    public ?string $due_at = null;

    public array $assigned_to = [];

    public ?string $client_id = null;

    public ?string $project_id = null;

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'due_at' => $this->task ? ['nullable', 'date'] : ['nullable', 'date', 'after:yesterday'],
            'assigned_to' => ['nullable', 'array'],
            'assigned_to.*' => $this->assigneeRule(),
            'client_id' => ['nullable', $this->clientRule()],
            'project_id' => ['nullable', $this->projectRule()],
        ];
    }

    private function assigneeRule(): Exists
    {
        return Rule::exists('users', 'id')->where(function ($query) {
            $query->where('is_active', true)->whereNotNull('email_verified_at');
            if ($this->task) {
                $query->orWhereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('task_user')
                        ->whereColumn('task_user.user_id', 'users.id')
                        ->where('task_user.task_id', $this->task->id);
                });
            }
        });
    }

    private function clientRule(): Exists
    {
        return $this->task
            ? Rule::exists('clients', 'id')
            : Rule::exists('clients', 'id')->where('status', ClientStatus::Active->value)->whereNull('deleted_at');
    }

    private function projectRule(): Exists
    {
        return Rule::exists('projects', 'id')->whereNull('deleted_at')->when($this->client_id, function ($query) {
            $query->where('client_id', $this->client_id);
        });
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Task Title is required.',
            'title.min' => 'Task Title must be at least 2 characters.',
            'title.max' => 'Task Title cannot exceed 255 characters.',
            'status.required' => 'Status is required.',
            'priority.required' => 'Priority is required.',
            'due_at.date' => 'Please enter a valid Date & Time.',
            'due_at.after' => 'Due date cannot be in the past.',
            'assigned_to.*.exists' => 'Selected Assignee is invalid.',
            'client_id.exists' => 'Selected Client is invalid.',
            'project_id.exists' => 'Selected Project is invalid.',
        ];
    }

    #[On('create-task')]
    public function openCreateModal(?string $clientId = null, ?string $projectId = null): void
    {
        $this->authorize('create', Task::class);

        if ($this->task !== null) {
            $this->resetDefaults();
        }

        $this->resetValidation();

        if ($clientId) {
            $this->client_id = $clientId;
        }

        if ($projectId) {
            $this->project_id = $projectId;
        }

        $this->showModal = true;
    }

    #[On('edit-task')]
    public function openEditModal(string $id): void
    {
        $task = Task::with('assignees')->findOrFail($id);
        $this->authorize('update', $task);

        $this->resetDefaults();
        $this->fillFromModel($task);
        $this->title = $task->title;
        $this->description = $task->description;
        $this->status = $task->status->value;
        $this->priority = $task->priority->value;
        $this->due_at = $task->due_at?->format('Y-m-d\TH:i');
        $this->assigned_to = $task->assignees->pluck('id')->toArray();
        $this->client_id = $task->client_id;
        $this->project_id = $task->project_id;

        $this->showModal = true;
    }

    public function updatedClientId($value): void
    {
        if ($this->project_id) {
            $project = Project::query()->select('client_id')->find($this->project_id);
            if ($project && (string) $project->client_id !== (string) $value) {
                $this->project_id = null;
            }
        }
    }

    public function updatedProjectId($value): void
    {
        if ($value) {
            $project = Project::query()->select('client_id')->find($value);
            if ($project) {
                $this->client_id = $project->client_id;
            }
        }
    }

    public function save(TaskManager $manager): void
    {
        $this->authorizeSave();
        $this->sanitize();
        $validated = $this->validate();
        $taskData = $this->taskData($validated);

        if ($this->task !== null) {
            $this->updateTask($manager, $taskData, $validated['assigned_to'] ?? []);

            return;
        }

        $this->storeTask($manager, $taskData, $validated['assigned_to'] ?? []);
    }

    private function authorizeSave(): void
    {
        if ($this->task) {
            $this->authorize('update', $this->task);

            return;
        }

        $this->authorize('create', Task::class);
    }

    private function updateTask(TaskManager $manager, array $taskData, array $assignees): void
    {
        $manager->update($this->task, $taskData, $assignees);
        Flux::toast('Task updated successfully.', variant: 'success');
        $this->finish();
    }

    private function storeTask(TaskManager $manager, array $taskData, array $assignees): void
    {
        $manager->create($taskData, $assignees);
        Flux::toast('Task created successfully.', variant: 'success');
        $this->finish();
    }

    private function sanitize(): void
    {
        $this->title = trim($this->title);
        $this->description = $this->description === null ? null : trim($this->description);
    }

    private function taskData(array $validated): array
    {
        return [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'due_at' => $validated['due_at'],
            'client_id' => $validated['client_id'] ?: null,
            'project_id' => $validated['project_id'] ?: null,
        ];
    }

    private function finish(): void
    {
        $this->resetDefaults();
        $this->showModal = false;
        $this->dispatch('tasks.saved');
    }

    private function resetDefaults(): void
    {
        $this->reset([
            'title',
            'description',
            'due_at',
            'assigned_to',
            'client_id',
            'project_id',
            'task',
        ]);

        $this->status = TaskStatus::Todo->value;
        $this->priority = TaskPriority::Medium->value;
        $this->resetValidation();
    }

    private function fillFromModel(Task $task): void
    {
        $this->task = $task;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->status = $task->status->value;
        $this->priority = $task->priority->value;
        $this->due_at = $task->due_at?->format('Y-m-d\TH:i');
        $this->assigned_to = $task->assignees->pluck('id')->toArray();
        $this->client_id = $task->client_id;
        $this->project_id = $task->project_id;
    }

    #[Computed]
    public function statuses(): array
    {
        return TaskStatus::cases();
    }

    #[Computed]
    public function priorities(): array
    {
        return TaskPriority::cases();
    }

    #[Computed]
    public function users(): Collection
    {
        return User::query()
            ->where(function ($query) {
                $query->where('is_active', true)
                    ->whereNotNull('email_verified_at')
                    ->where('role', '!=', UserRole::Admin->value)
                    ->when(auth()->user()->isManager(), fn ($q) => $q->where('role', '!=', UserRole::Manager->value));

                if ($this->task && ! empty($this->assigned_to)) {
                    $query->orWhereIn('id', $this->assigned_to);
                }
            })
            ->select('id', 'name', 'role')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function clients(): Collection
    {
        return Client::where('status', ClientStatus::Active->value)
            ->select('id', 'company_name', 'client_name')
            ->orderBy('company_name')
            ->get();
    }

    #[Computed]
    public function projects(): Collection
    {
        return Project::where(function ($query) {
            $query->where('status', '!=', ProjectStatus::Completed->value);
            if ($this->project_id) {
                $query->orWhere('id', $this->project_id);
            }
        })
            ->when($this->client_id, fn ($q) => $q->where('client_id', $this->client_id))
            ->select('id', 'project_name')
            ->orderBy('project_name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.tasks.task-form');
    }
}
