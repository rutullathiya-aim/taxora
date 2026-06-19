<?php

declare(strict_types=1);

namespace App\Livewire\Projects;

use App\Enums\ClientStatus;
use App\Enums\CrudAction;
use App\Enums\ProjectStatus;
use App\Enums\ResourceType;
use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use App\Services\ProjectManager;
use App\Support\Toast;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ProjectForm extends Component
{
    public bool $showModal = false;

    public ?Project $project = null;

    public ?string $client_id = null;

    public string $project_name = '';

    public array $assignees = [];

    public ?string $service_id = null;

    public string $status = ProjectStatus::Active->value;

    public ?string $due_date = null;

    public function rules(): array
    {
        $rules = [
            'client_id' => [
                'required',
                $this->project
                    ? Rule::exists('clients', 'id')
                    : Rule::exists('clients', 'id')->where('status', ClientStatus::Active->value)->whereNull('deleted_at'),
            ],
            'project_name' => ['required', 'string', 'min:2', 'max:150'],
            'assignees' => 'nullable|array',
            'assignees.*' => [
                'distinct',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('is_active', true)->whereNotNull('email_verified_at');
                    if ($this->project) {
                        $query->orWhereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('project_user')
                                ->whereColumn('project_user.user_id', 'users.id')
                                ->where('project_user.project_id', $this->project->id);
                        });
                    }
                }),
            ],
            'service_id' => [
                'required',
                $this->project
                    ? Rule::exists('services', 'id')
                    : Rule::exists('services', 'id')->where('status', ServiceStatus::Active->value)->whereNull('deleted_at'),
            ],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'due_date' => ['nullable', 'date'],
        ];

        if (! $this->project) {
            $rules['due_date'][] = 'after_or_equal:today';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Please select a Client.',
            'project_name.required' => 'Project Name is required.',
            'project_name.min' => 'Project Name must be at least 2 characters.',
            'service_id.required' => 'Please select a Service.',
            'status.required' => 'Status is required.',
            'due_date.after_or_equal' => 'Due date cannot be in the past.',
        ];
    }

    #[On('create-project')]
    public function openCreateModal(?string $clientId = null): void
    {
        $this->authorize('create', Project::class);
        if ($this->project !== null) {
            $this->resetForm();
        }
        $this->resetValidation();
        if ($clientId) {
            $this->client_id = $clientId;
        }
        $this->showModal = true;
    }

    #[On('edit-project')]
    public function openEditModal(string $id): void
    {
        $project = Project::with('assignees')->findOrFail($id);
        $this->authorize('update', $project);
        $this->resetForm();
        $this->fillFromModel($project);
        $this->showModal = true;
    }

    public function save(ProjectManager $manager): void
    {
        $this->authorizeSave();
        $this->sanitize();
        $validated = $this->validate();

        if ($this->project !== null) {
            $this->updateProject($manager, $validated);

            return;
        }

        $this->storeProject($manager, $validated);
    }

    private function authorizeSave(): void
    {
        if ($this->project) {
            $this->authorize('update', $this->project);

            return;
        }

        $this->authorize('create', Project::class);
    }

    private function updateProject(ProjectManager $manager, array $validated): void
    {
        $manager->update(
            project: $this->project,
            data: $this->projectData($validated),
            assigneeIds: $validated['assignees'] ?? []
        );

        Toast::success(CrudAction::Updated, ResourceType::Project);
        $this->finish();
    }

    private function storeProject(ProjectManager $manager, array $validated): void
    {
        $manager->create(
            data: $this->projectData($validated, includeService: true),
            assigneeIds: $validated['assignees'] ?? []
        );

        Toast::success(CrudAction::Created, ResourceType::Project);
        $this->finish();
    }

    public function resetForm(): void
    {
        $this->resetDefaults();
        $this->resetValidation();
    }

    #[Computed]
    public function clients(): Collection
    {
        if (! $this->showModal) {
            return collect();
        }

        return Client::query()
            ->when($this->project && $this->client_id, function ($q) {
                $q->where(function ($sub) {
                    $sub->status(ClientStatus::Active)
                        ->orWhere('id', $this->client_id);
                });
            }, fn ($q) => $q->status(ClientStatus::Active))
            ->select('id', 'client_name', 'company_name')
            ->orderBy('company_name')
            ->orderBy('client_name')
            ->get();
    }

    #[Computed]
    public function staffMembers(): Collection
    {
        if (! $this->showModal) {
            return collect();
        }

        return User::query()
            ->where(function ($query) {
                $query
                    ->where('is_active', true)
                    ->whereNotNull('email_verified_at')
                    ->where('role', '!=', UserRole::Admin->value)
                    ->when(auth()->user()->isManager(), fn ($q) => $q->where('role', '!=', UserRole::Manager->value));

                if ($this->project && ! empty($this->assignees)) {
                    $query->orWhereIn('id', $this->assignees);
                }
            })
            ->select('id', 'name', 'role')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function services(): Collection
    {
        if (! $this->showModal) {
            return collect();
        }

        return Service::query()
            ->when($this->project && $this->service_id, function ($q) {
                $q->where(function ($sub) {
                    $sub->status(ServiceStatus::Active)
                        ->orWhere('id', $this->service_id);
                });
            }, fn ($q) => $q->status(ServiceStatus::Active))
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    private function fillFromModel(Project $project): void
    {
        $this->project = $project;
        $this->client_id = $project->client_id;
        $this->project_name = $project->project_name;
        $this->assignees = $project->assignees->pluck('id')->toArray();
        $this->service_id = $project->service_id;
        $this->status = $project->status->value;
        $this->due_date = $project->due_date ? $project->due_date->format('Y-m-d') : null;
    }

    private function finish(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('projects.saved');
    }

    private function resetDefaults(): void
    {
        $this->reset([
            'client_id',
            'project_name',
            'assignees',
            'service_id',
            'due_date',
            'project',
        ]);

        $this->status = ProjectStatus::Active->value;
    }

    private function sanitize(): void
    {
        $this->project_name = trim($this->project_name);
        $this->due_date = blank($this->due_date) ? null : trim($this->due_date);
    }

    private function projectData(array $validated, bool $includeService = false): array
    {
        $data = [
            'client_id' => $validated['client_id'],
            'project_name' => $validated['project_name'],
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? null,
        ];

        if ($includeService) {
            $data['service_id'] = $validated['service_id'];
        }

        return $data;
    }

    public function render(): View
    {
        return view('livewire.projects.project-form');
    }
}
