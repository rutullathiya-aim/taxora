<?php

namespace App\Livewire\Projects;

use App\Enums\ChecklistStatus;
use App\Models\Document;
use App\Models\ProjectChecklist;
use App\Models\ProjectChecklistDocument;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HasProjectChecklistActions
{
    use AuthorizesRequests;

    public ?string $previewDocumentId = null;

    public bool $showViewerModal = false;

    public ?string $removeDocumentId = null;

    public bool $showRemoveDocumentModal = false;

    public bool $deleteDocumentGlobally = false;

    public bool $isDocumentTrashed = false;

    private const LOCKED_STATUSES = [
        ChecklistStatus::Submitted,
        ChecklistStatus::Approved,
    ];

    // --- Checklist Actions ---

    public function updateStatus(string $checklistId, string $statusValue): void
    {
        $this->authorize('update', $this->project);

        $status = ChecklistStatus::tryFrom($statusValue);

        if (! $status) {
            abort(400);
        }

        $checklist = $this->loadChecklist($checklistId);

        if (! $this->ensureChecklistCanBeUpdated($checklist, $status)) {
            return;
        }

        $updateData = $this->applyApprovalFields(['status' => $status], $status);

        $checklist->update($updateData);
        $this->success('Checklist status updated.');
        $this->runAfterProjectChecklistAction('updateStatus', $checklist);
    }

    #[On('delete-checklist')]
    public function deleteChecklist(string $id): void
    {
        $this->authorize('update', $this->project);

        $checklist = $this->loadChecklist($id);

        if (! $checklist->is_manual) {
            $this->danger('Cannot delete a default checklist item.');

            return;
        }

        $checklist->delete();
        $this->success('Checklist item deleted.');
        $this->runAfterProjectChecklistAction('deleteChecklist', $checklist);
    }

    private function loadChecklist(string $id): ProjectChecklist
    {
        return $this->project
            ->checklists()
            ->with('documents.Document')
            ->findOrFail($id);
    }

    private function ensureChecklistCanBeUpdated(ProjectChecklist $checklist, ChecklistStatus $status): bool
    {
        if ($checklist->is_mandatory && ! $this->hasActiveDocuments($checklist) && in_array($status, self::LOCKED_STATUSES, true)) {
            $this->danger('An active document is required for mandatory checklists before updating this status.');

            return false;
        }

        return true;
    }

    private function applyApprovalFields(array $updateData, ChecklistStatus $status): array
    {
        if ($status === ChecklistStatus::Approved) {
            $updateData['approved_at'] = now();
            $updateData['approved_by'] = auth()->id() ?? abort(403, 'Must be logged in to approve');
        } else {
            $updateData['approved_at'] = null;
            $updateData['approved_by'] = null;
        }

        return $updateData;
    }

    // --- Document Actions ---

    #[On('view-document')]
    public function viewDocument(string $documentId): void
    {
        $this->authorize('view', $this->project);
        $clientDoc = $this->getProjectDocument($documentId);

        $this->previewDocumentId = $documentId;
        $this->showViewerModal = true;
    }

    #[Computed]
    public function previewDocument(): ?Document
    {
        if (! $this->previewDocumentId) {
            return null;
        }

        return $this->getProjectDocument($this->previewDocumentId);
    }

    public function downloadDocument(string $documentId): ?StreamedResponse
    {
        $this->authorize('view', $this->project);
        $clientDoc = $this->getProjectDocument($documentId);

        if (! Storage::exists($clientDoc->path)) {
            $this->danger('Document not found.');

            return null;
        }

        return Storage::download($clientDoc->path, $clientDoc->name);
    }

    public function openRemoveDocumentModal(string $id): void
    {
        $document = $this->loadChecklistDocument($id);

        $this->isDocumentTrashed = $document->Document ? $document->Document->trashed() : true;

        $this->removeDocumentId = $id;
        $this->deleteDocumentGlobally = false;
        $this->showRemoveDocumentModal = true;
    }

    public function confirmRemoveDocument(): void
    {
        $this->authorize('update', $this->project);

        $document = $this->loadChecklistDocument($this->removeDocumentId);

        $checklist = $document->projectChecklist;
        $clientDoc = $document->Document;

        if (
            $this->deleteDocumentGlobally &&
            $clientDoc &&
            $clientDoc->isLockedByApprovedChecklist($document->id)
        ) {
            $this->danger('Cannot delete globally because this document is still attached to another Submitted or Approved checklist.');
            $this->closeRemoveDocumentModal();

            return;
        }

        $document->delete();

        $checklist->load('documents.Document');
        $reverted = false;

        if (! $this->hasActiveDocuments($checklist) && in_array($checklist->status, self::LOCKED_STATUSES, true)) {
            $checklist->update(['status' => ChecklistStatus::Pending]);
            $reverted = true;
        }

        if ($this->deleteDocumentGlobally && $clientDoc) {
            $clientDoc->delete();
            $this->success($reverted ? 'Document deleted globally and checklist reverted to Pending.' : 'Document deleted globally.');
        } else {
            $this->success($reverted ? 'Document unlinked and checklist reverted to Pending.' : 'Document unlinked successfully.');
        }

        $this->closeRemoveDocumentModal();
        $this->runAfterProjectChecklistAction('confirmRemoveDocument', $checklist);
    }

    private function closeRemoveDocumentModal(): void
    {
        $this->showRemoveDocumentModal = false;
        $this->removeDocumentId = null;
        $this->deleteDocumentGlobally = false;
        $this->isDocumentTrashed = false;
    }

    private function getProjectDocument(string $id): Document
    {
        $document = Document::withTrashed()->findOrFail($id);

        abort_if(
            $document->client_id !== $this->project->client_id,
            403
        );

        return $document;
    }

    private function loadChecklistDocument(string $id): ProjectChecklistDocument
    {
        return ProjectChecklistDocument::with('Document')
            ->whereHas('projectChecklist', fn ($query) => $query->where('project_id', $this->project->id))
            ->findOrFail($id);
    }

    // --- Shared Helpers ---

    private function hasActiveDocuments(ProjectChecklist $checklist): bool
    {
        return $checklist->documents->contains(fn ($doc) => $doc->Document && ! $doc->Document->trashed());
    }

    private function runAfterProjectChecklistAction(string $action, ?ProjectChecklist $checklist = null): void
    {
        if (method_exists($this, 'afterProjectChecklistAction')) {
            $this->afterProjectChecklistAction($action, $checklist);
        }
    }

    private function success(string $message): void
    {
        Flux::toast($message, variant: 'success');
    }

    private function danger(string $message): void
    {
        Flux::toast($message, variant: 'danger');
    }
}
