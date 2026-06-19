<?php

namespace App\Livewire\Projects;

use App\Enums\ChecklistStatus;
use App\Models\Document;
use App\Models\ProjectChecklist;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class VaultModal extends Component
{
    use WithFileUploads;

    public bool $showModal = false;

    public ?string $checklistId = null;

    public string $vaultSearch = '';

    public array $selectedVaultDocumentIds = [];

    public array $alreadyAttachedDocumentIds = [];

    public $newDocuments = [];

    #[On('open-vault-modal')]
    public function openVaultModal(string $checklistId): void
    {
        $this->checklistId = $checklistId;
        $checklist = ProjectChecklist::findOrFail($checklistId);

        $this->alreadyAttachedDocumentIds = $checklist->documents()->pluck('document_id')->toArray();
        $this->selectedVaultDocumentIds = $this->alreadyAttachedDocumentIds;
        $this->vaultSearch = '';
        $this->newDocuments = [];

        $this->showModal = true;
    }

    public function updatedNewDocuments()
    {
        if (empty($this->newDocuments)) {
            return;
        }

        $checklist = ProjectChecklist::with('project')->findOrFail($this->checklistId);

        $selected = $this->selectedVaultDocumentIds;

        foreach ($this->newDocuments as $file) {
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();

            $path = $file->store('client-documents/' . $checklist->project->client_id, 'local');

            $clientDoc = Document::create([
                'client_id' => $checklist->project->client_id,
                'name' => $originalName,
                'path' => $path,
                'size' => $size,
                'mime_type' => $mimeType,
            ]);

            $selected[] = (string) $clientDoc->id;
        }

        $this->selectedVaultDocumentIds = array_values(array_unique($selected));

        $this->newDocuments = [];
        $this->dispatch('documents-uploaded'); // Just a notification if needed
    }

    public function removeSelection(string $id): void
    {
        $this->selectedVaultDocumentIds = array_values(array_diff($this->selectedVaultDocumentIds, [$id]));
    }

    public function confirmVaultSelection(): void
    {
        $checklist = ProjectChecklist::findOrFail($this->checklistId);

        $currentlyAttached = $checklist->documents()->pluck('document_id')->toArray();

        $toAttach = array_diff($this->selectedVaultDocumentIds, $currentlyAttached);
        $toDetach = array_diff($currentlyAttached, $this->selectedVaultDocumentIds);

        if (! empty($toDetach)) {
            if (in_array($checklist->status, [ChecklistStatus::Submitted, ChecklistStatus::Approved])) {
                Flux::toast('Cannot remove documents from a Submitted or Approved checklist.', variant: 'danger');

                return;
            }
            $checklist->documents()->whereIn('document_id', $toDetach)->delete();
        }

        foreach ($toAttach as $clientDocId) {
            $exists = Document::where('client_id', $checklist->project->client_id)
                ->where('id', $clientDocId)->exists();

            if ($exists) {
                $checklist->documents()->create([
                    'document_id' => $clientDocId,
                ]);
            }
        }

        $this->showModal = false;
        $this->checklistId = null;

        $this->dispatch('checklist-updated');
        Flux::toast('Document(s) attached successfully.', variant: 'success');
    }

    public function viewDocument(string $id): void
    {
        $this->dispatch('view-document', documentId: $id);
    }

    public function render()
    {
        $Documents = collect();
        if ($this->checklistId) {
            $checklist = ProjectChecklist::with('project')->find($this->checklistId);
            if ($checklist) {
                $Documents = Document::where('client_id', $checklist->project->client_id)
                    ->when($this->vaultSearch, function ($query) {
                        $query->where('name', 'like', '%' . $this->vaultSearch . '%');
                    })
                    ->latest()
                    ->get();
            }
        }

        return view('livewire.projects.vault-modal', [
            'Documents' => $Documents,
        ]);
    }
}
