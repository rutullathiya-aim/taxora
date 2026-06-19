<?php

namespace App\Livewire\Documents;

use App\Enums\DocumentAction;
use App\Models\Document;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HasDocumentActions
{
    private const DOCUMENT_NAME_REGEX = '/^[\pL\pN\s\-_.,&()]+$/u';

    public bool $showPreviewModal = false;

    public ?string $previewDocumentId = null;

    public bool $showRenameModal = false;

    public ?string $renameDocumentId = null;

    public string $renameDocumentName = '';

    #[Computed]
    public function previewDocument(): ?Document
    {
        if (! $this->previewDocumentId) {
            return null;
        }

        return Document::find($this->previewDocumentId);
    }

    public function viewDocument(string $id): void
    {
        $document = $this->getDocument($id);

        $this->previewDocumentId = $document->id;
        $this->showPreviewModal = true;
    }

    public function closePreviewModal(): void
    {
        $this->showPreviewModal = false;
        $this->previewDocumentId = null;
    }

    #[On('delete-document')]
    public function deleteDocument(string $id): void
    {
        $document = $this->getDocument($id);
        $this->authorize('delete', $document);

        if (! $this->ensureDocumentCanBeDeleted($document)) {
            return;
        }

        $document->delete();

        $this->invokeAfterDocumentAction(DocumentAction::Delete, $document);
        $this->success('Document deleted successfully.');
    }

    #[On('restore-document')]
    public function restoreDocument(string $id): void
    {
        $document = $this->getDocument($id);
        $this->authorize('restore', $document);

        $document->restore();

        $this->invokeAfterDocumentAction(DocumentAction::Restore, $document);
        $this->success('Document restored successfully.');
    }

    #[On('force-delete-document')]
    public function forceDeleteDocument(string $id): void
    {
        $document = $this->getDocument($id);
        $this->authorize('forceDelete', $document);

        if (! $this->ensureDocumentCanBeDeleted($document)) {
            return;
        }

        $document->deleteFile();

        $document->forceDelete();

        $this->invokeAfterDocumentAction(DocumentAction::ForceDelete, $document);
        $this->success('Document permanently deleted.');
    }

    public function downloadDocument(string $id): ?StreamedResponse
    {
        $document = $this->getDocument($id);

        $response = $document->download();

        if (! $response) {
            $this->danger('Document not found on server.');

            return null;
        }

        return $response;
    }

    public function openRenameModal(string $id): void
    {
        $this->resetValidation();

        $document = $this->getDocument($id);
        $this->authorize('update', $document);

        $this->renameDocumentId = $document->id;
        $this->renameDocumentName = pathinfo($document->name, PATHINFO_FILENAME);
        $this->showRenameModal = true;
    }

    public function closeRenameModal(): void
    {
        $this->showRenameModal = false;
        $this->renameDocumentId = null;
        $this->renameDocumentName = '';
        $this->resetValidation();
    }

    public function saveDocumentName(): void
    {
        if (! $this->renameDocumentId) {
            return;
        }

        $document = $this->getDocument($this->renameDocumentId);
        $this->authorize('update', $document);

        $this->renameDocumentName = trim($this->renameDocumentName);

        $this->validate([
            'renameDocumentName' => [
                'required',
                'string',
                'max:255',
                'regex:' . self::DOCUMENT_NAME_REGEX,
            ],
        ], [
            'renameDocumentName.required' => 'Document name is required.',
            'renameDocumentName.max' => 'Document name may not exceed 255 characters.',
            'renameDocumentName.regex' => 'Document name contains invalid characters.',
        ]);

        $ext = pathinfo($document->name, PATHINFO_EXTENSION);
        $newName = $this->renameDocumentName;

        if (filled($ext)) {
            $newName .= '.' . $ext;
        }

        if ($document->name === $newName) {
            $this->closeRenameModal();

            return;
        }

        $exists = Document::where('client_id', $document->client_id)
            ->where('id', '!=', $document->id)
            ->where('name', $newName)
            ->exists();

        if ($exists) {
            $this->addError(
                'renameDocumentName',
                'A document with this name already exists.'
            );

            return;
        }

        $document->update(['name' => $newName]);

        $this->closeRenameModal();

        $this->invokeAfterDocumentAction(DocumentAction::Rename, $document);
        $this->success('Document renamed successfully.');
    }

    private function ensureDocumentCanBeDeleted(Document $document): bool
    {
        if (! $document->isLockedByApprovedChecklist()) {
            return true;
        }

        $this->danger('Cannot delete a document attached to a Submitted or Approved checklist.');

        return false;
    }

    private function invokeAfterDocumentAction(DocumentAction $action, Document $document): void
    {
        if (method_exists($this, 'afterDocumentAction')) {
            $this->afterDocumentAction($action, $document);
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
