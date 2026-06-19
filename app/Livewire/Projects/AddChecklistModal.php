<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Attributes\On;
use Livewire\Component;

class AddChecklistModal extends Component
{
    public bool $showModal = false;

    public ?string $projectId = null;

    public string $newDocumentName = '';

    public bool $newDocumentMandatory = false;

    #[On('open-add-checklist-modal')]
    public function openModal(string $projectId): void
    {
        $this->projectId = $projectId;
        $this->newDocumentName = '';
        $this->newDocumentMandatory = false;
        $this->showModal = true;
    }

    public function saveNewDocument(): void
    {
        $this->newDocumentName = trim($this->newDocumentName);
        $this->validate([
            'newDocumentName' => 'required|string|min:2|max:255',
        ], [
            'newDocumentName.required' => 'Checklist name is required.',
            'newDocumentName.min' => 'Checklist name must be at least 2 characters.',
        ]);

        $project = Project::findOrFail($this->projectId);

        $project->checklists()->create([
            'name' => $this->newDocumentName,
            'is_mandatory' => $this->newDocumentMandatory,
            'is_manual' => true,
            'status' => 'Pending',
        ]);

        $this->showModal = false;
        $this->newDocumentName = '';
        $this->newDocumentMandatory = false;
        $this->projectId = null;

        $this->dispatch('checklist-updated');
    }

    public function render()
    {
        return view('livewire.projects.add-checklist-modal');
    }
}
