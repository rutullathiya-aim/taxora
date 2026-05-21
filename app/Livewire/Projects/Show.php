<?php

namespace App\Livewire\Projects;

use App\Models\ClientDocument;
use App\Models\Project;
use App\Models\ProjectChecklist;
use App\Models\ProjectChecklistDocument;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public Project $project;

    public bool $showVaultModal = false;

    public ?string $uploadingChecklistId = null;

    public string $vaultSearch = '';

    public ?string $previewDocumentId = null;

    public array $selectedVaultDocumentIds = [];

    public array $alreadyAttachedDocumentIds = [];

    public $newDocuments = [];

    public string $search = '';

    public string $statusFilter = 'all';

    public bool $showRemarksModal = false;

    public ?string $remarksChecklistId = null;

    public string $remarks = '';

    public array $selectedChecklists = [];

    public ?string $viewerDocumentId = null;

    public bool $showViewerModal = false;

    public bool $selectAll = false;

    /** @var array<string> */
    public array $statuses = [
        'Pending',
        'Submitted',
        'Approved',
        'Rejected',
        'Not Applicable',
    ];

    public function mount(Project $project): void
    {
        $this->project = $project->load(['client', 'service', 'projectChecklists.documents']);
    }

    public function openVaultModal(string $checklistId): void
    {
        $this->uploadingChecklistId = $checklistId;

        $checklist = ProjectChecklist::with('documents')->findOrFail($checklistId);
        abort_if($checklist->project_id !== $this->project->id, 403);

        $this->alreadyAttachedDocumentIds = $checklist->documents->pluck('client_document_id')->toArray();
        $this->selectedVaultDocumentIds = $this->alreadyAttachedDocumentIds;

        $this->newDocuments = [];
        $this->vaultSearch = '';
        $this->previewDocumentId = null;
        $this->resetValidation();

        if (! empty($this->selectedVaultDocumentIds)) {
            $this->previewDocumentId = $this->selectedVaultDocumentIds[0];
        }

        $this->showVaultModal = true;
    }

    public function updatedNewDocuments(): void
    {
        $this->validate([
            'newDocuments.*' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
        ]);

        foreach ($this->newDocuments as $doc) {
            $originalName = $doc->getClientOriginalName();
            $mimeType = $doc->getMimeType();
            $size = $doc->getSize();

            $path = $doc->store('client-documents/'.$this->project->client_id, 'local');

            $clientDoc = ClientDocument::create([
                'client_id' => $this->project->client_id,
                'name' => $originalName,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'size' => $size,
                'path' => $path,
            ]);

            if (! in_array($clientDoc->id, $this->selectedVaultDocumentIds)) {
                $this->selectedVaultDocumentIds[] = $clientDoc->id;
            }

            if ($doc === $this->newDocuments[0]) {
                $this->previewDocumentId = $clientDoc->id;
            }
        }

        $this->newDocuments = [];
    }

    public function removeSelection(string $id): void
    {
        $this->selectedVaultDocumentIds = array_values(array_diff($this->selectedVaultDocumentIds, [$id]));
    }

    public function setPreviewDocument(string $id): void
    {
        $this->previewDocumentId = $id;
    }

    public function confirmVaultSelection(): void
    {
        $checklist = ProjectChecklist::findOrFail($this->uploadingChecklistId);
        abort_if($checklist->project_id !== $this->project->id, 403);

        $currentlyAttached = $checklist->documents()->pluck('client_document_id')->toArray();

        $toAttach = array_diff($this->selectedVaultDocumentIds, $currentlyAttached);
        $toDetach = array_diff($currentlyAttached, $this->selectedVaultDocumentIds);

        if (! empty($toDetach)) {
            $checklist->documents()->whereIn('client_document_id', $toDetach)->delete();
        }

        foreach ($toAttach as $clientDocId) {
            $exists = ClientDocument::where('client_id', $this->project->client_id)
                ->where('id', $clientDocId)->exists();

            if ($exists) {
                $checklist->documents()->create([
                    'client_document_id' => $clientDocId,
                ]);
            }
        }

        if ($checklist->status === 'Pending' && $checklist->documents()->count() > 0) {
            $checklist->update(['status' => 'Submitted']);
        } elseif ($checklist->documents()->count() === 0 && in_array($checklist->status, ['Submitted', 'Approved', 'Rejected'])) {
            $checklist->update(['status' => 'Pending']);
        }

        $this->showVaultModal = false;
        $this->uploadingChecklistId = null;
        $this->project->refresh();
    }

    public function updateStatus(string $checklistId, string $status): void
    {

        if (! in_array($status, $this->statuses)) {
            abort(400);
        }

        $checklist = ProjectChecklist::findOrFail($checklistId);
        abort_if($checklist->project_id !== $this->project->id, 403);

        $updateData = ['status' => $status];

        if ($status === 'Approved') {
            $updateData['approved_at'] = now();
            $updateData['approved_by'] = auth()->id() ?? abort(403, 'Must be logged in to approve');
        }

        $checklist->update($updateData);
        $this->project->refresh();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedChecklists = $this->project->projectChecklists()
                ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
                ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedChecklists = [];
        }
    }

    public function updatedSelectedChecklists(): void
    {
        // When user manually unchecks an item, uncheck "select all"
        $this->selectAll = false;
    }

    public function bulkUpdateStatus(string $status): void
    {
        if (! in_array($status, $this->statuses) || empty($this->selectedChecklists)) {
            return;
        }

        $checklists = ProjectChecklist::whereIn('id', $this->selectedChecklists)
            ->where('project_id', $this->project->id)
            ->get();

        foreach ($checklists as $checklist) {
            $updateData = ['status' => $status];
            if ($status === 'Approved') {
                $updateData['approved_at'] = now();
                $updateData['approved_by'] = auth()->id() ?? abort(403);
            }
            $checklist->update($updateData);
        }

        $this->selectedChecklists = [];
        $this->selectAll = false;
        $this->project->refresh();
    }

    public function openRemarksModal(string $checklistId): void
    {
        $checklist = ProjectChecklist::findOrFail($checklistId);
        abort_if($checklist->project_id !== $this->project->id, 403);
        $this->remarksChecklistId = $checklistId;
        $this->remarks = $checklist->remarks ?? '';
        $this->showRemarksModal = true;
    }

    public function saveRemarks(): void
    {

        $checklist = ProjectChecklist::findOrFail($this->remarksChecklistId);
        abort_if($checklist->project_id !== $this->project->id, 403);

        $checklist->update([
            'remarks' => $this->remarks,
        ]);

        $this->remarksChecklistId = null;
        $this->remarks = '';
        $this->showRemarksModal = false;
        $this->project->refresh();
    }

    public function viewDocument(string $documentId): void
    {
        $this->viewerDocumentId = $documentId;
        $this->showViewerModal = true;
    }

    public function downloadDocument(string $documentId)
    {
        $document = ProjectChecklistDocument::with('clientDocument')->findOrFail($documentId);
        abort_if($document->projectChecklist->project_id !== $this->project->id, 403);

        $clientDoc = $document->clientDocument;

        if (! Storage::disk('local')->exists($clientDoc->path)) {
            session()->flash('error', 'Document not found.');

            return;
        }

        return Storage::disk('local')->download($clientDoc->path, $clientDoc->name);
    }

    public function deleteDocument(string $documentId): void
    {
        $document = ProjectChecklistDocument::findOrFail($documentId);
        $checklist = $document->projectChecklist;
        abort_if($checklist->project_id !== $this->project->id, 403);

        $document->delete();

        if ($checklist->documents()->count() === 0 && in_array($checklist->status, ['Submitted', 'Approved', 'Rejected'])) {
            $checklist->update(['status' => 'Pending']);
        }

        $this->project->refresh();
    }

    public function render()
    {
        $checklists = $this->project->projectChecklists()
            ->with('documents')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->get();

        $clientDocuments = ClientDocument::where('client_id', $this->project->client_id)
            ->when($this->vaultSearch, function ($query) {
                $query->where('name', 'like', '%'.$this->vaultSearch.'%');
            })
            ->latest()
            ->get();

        return view('livewire.projects.show', [
            'checklists' => $checklists,
            'clientDocuments' => $clientDocuments,
        ])->layout('components.layouts.app');
    }
}
