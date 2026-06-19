<?php

namespace App\Livewire\Projects;

use App\Models\ProjectChecklist;
use Livewire\Attributes\On;
use Livewire\Component;

class RemarksModal extends Component
{
    public bool $showModal = false;

    public ?string $checklistId = null;

    public string $remarks = '';

    #[On('open-remarks-modal')]
    public function openRemarksModal(string $checklistId): void
    {
        $this->checklistId = $checklistId;
        $checklist = ProjectChecklist::findOrFail($checklistId);
        $this->remarks = $checklist->remarks ?? '';
        $this->showModal = true;
    }

    public function saveRemarks(): void
    {
        $this->remarks = trim($this->remarks);
        $this->validate([
            'remarks' => 'nullable|string|max:5000',
        ]);

        $checklist = ProjectChecklist::findOrFail($this->checklistId);
        $checklist->update(['remarks' => $this->remarks]);

        $this->showModal = false;
        $this->checklistId = null;

        $this->dispatch('checklist-updated');
    }

    public function render()
    {
        return view('livewire.projects.remarks-modal');
    }
}
