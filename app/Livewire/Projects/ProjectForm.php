<?php

namespace App\Livewire\Projects;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectChecklist;
use App\Models\Service;
use App\Models\ServiceChecklistItem;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

class ProjectForm extends Component
{
    public bool $showModal = false;

    public ?string $editingProjectId = null;

    public ?string $client_id = null;

    public string $project_name = '';

    public ?string $service_id = null;

    public string $status = 'in_progress';

    public ?string $due_date = null;

    #[On('create-project')]
    public function createProject(?string $clientId = null): void
    {
        if ($this->editingProjectId !== null) {
            $this->resetForm();
        }
        $this->resetValidation();

        if ($clientId) {
            $this->client_id = $clientId;
        }

        $this->showModal = true;
    }

    #[On('edit-project')]
    public function editProject(string $id): void
    {
        $this->resetForm();
        $project = Project::findOrFail($id);

        $this->editingProjectId = $project->id;
        $this->client_id = $project->client_id;
        $this->project_name = $project->project_name;
        $this->service_id = $project->service_id;
        $this->status = $project->status;
        $this->due_date = $project->due_date;

        $this->showModal = true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'project_name' => 'required|string|max:255',
            'service_id' => 'required|exists:services,id',
            'status' => 'required|string',
            'due_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Please select a Client.',
            'project_name.required' => 'Project Name is required.',
            'service_id.required' => 'Please select a Service.',
            'status.required' => 'Status is required.',
        ];
    }

    public function saveProject(): void
    {
        $this->validate();

        if ($this->editingProjectId) {
            $project = Project::findOrFail($this->editingProjectId);
            $project->update($this->only(['client_id', 'project_name', 'status', 'due_date']));
            Flux::toast('Project updated successfully.', variant: 'success');
        } else {
            $project = Project::create($this->only(['client_id', 'project_name', 'service_id', 'status', 'due_date']));

            // Auto-assign service checklist items
            $items = ServiceChecklistItem::where('service_id', $project->service_id)
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->get();

            foreach ($items as $item) {
                ProjectChecklist::create([
                    'project_id' => $project->id,
                    'name' => $item->title,
                    'is_mandatory' => $item->is_mandatory,
                    'status' => 'Pending',
                ]);
            }

            Flux::toast('Project created successfully.', variant: 'success');
        }

        $this->resetForm();
        $this->showModal = false;
        $this->dispatch('project-saved');
    }

    public function resetForm(): void
    {
        $this->reset([
            'client_id',
            'project_name',
            'service_id',
            'status',
            'due_date',
            'editingProjectId',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.projects.project-form', [
            'clients' => Client::orderBy('client_name')->get(),
            'services' => Service::where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}
