<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\ClientDocument;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Show extends Component
{
    use WithFileUploads, WithPagination;

    public Client $client;

    public string $currentTab = 'projects';

    // Project Filters
    public string $projectSearch = '';

    public string $projectStatusFilter = 'all';

    public string $projectSortBy = 'latest';

    // Document Filters
    public string $documentSearch = '';

    public string $documentSortBy = 'latest';

    // Document Upload
    public $newDocuments = [];

    // Modals
    public bool $showPreviewModal = false;

    public ?string $previewDocumentId = null;

    public bool $showDeleteModal = false;

    public ?string $deleteDocumentId = null;

    public function mount(Client $client): void
    {
        $this->client = $client->load(['projects.service', 'clientDocuments']);
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['projects', 'documents'])) {
            $this->currentTab = $tab;
        }
    }

    public function updatedNewDocuments(): void
    {
        $this->validate([
            'newDocuments.*' => 'required|file|max:20480', // 20MB limit
        ]);

        foreach ($this->newDocuments as $doc) {
            $originalName = $doc->getClientOriginalName();
            $mimeType = $doc->getMimeType();
            $size = $doc->getSize();

            $path = $doc->store('client-documents/'.$this->client->id, 'local');

            ClientDocument::create([
                'client_id' => $this->client->id,
                'name' => $originalName,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'size' => $size,
                'path' => $path,
            ]);
        }

        $this->newDocuments = [];
        $this->client->refresh();
        Flux::toast('Document(s) uploaded successfully.', variant: 'success');
    }

    public function viewDocument(string $id): void
    {
        $this->previewDocumentId = $id;
        $this->showPreviewModal = true;
    }

    public function closePreviewModal(): void
    {
        $this->showPreviewModal = false;
        $this->previewDocumentId = null;
    }

    public function confirmDelete(string $id): void
    {
        $this->deleteDocumentId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteDocument(): void
    {
        if (! $this->deleteDocumentId) {
            return;
        }

        $document = ClientDocument::findOrFail($this->deleteDocumentId);

        abort_if($document->client_id !== $this->client->id, 403);

        if (Storage::disk('local')->exists($document->path)) {
            Storage::disk('local')->delete($document->path);
        }

        $document->delete();

        $this->showDeleteModal = false;
        $this->deleteDocumentId = null;
        $this->client->refresh();

        Flux::toast('Document deleted successfully.', variant: 'success');
    }

    public function downloadDocument(string $id)
    {
        $document = ClientDocument::findOrFail($id);
        abort_if($document->client_id !== $this->client->id, 403);

        if (! Storage::disk('local')->exists($document->path)) {
            Flux::toast('Document not found on server.', variant: 'danger');

            return;
        }

        return Storage::disk('local')->download($document->path, $document->name);
    }

    public function deleteClient(string $id)
    {
        abort_if($this->client->id !== $id, 403);

        $this->client->delete();

        Flux::toast('Client moved to trash.', variant: 'success');

        return $this->redirect(route('clients.index'), navigate: true);
    }

    #[On('client-saved')]
    public function refreshClient(): void
    {
        $this->client->refresh();
        $this->dispatch('update-heading', $this->client->client_name);
    }

    public function updatingProjectSearch(): void
    {
        $this->resetPage();
    }

    public function updatingProjectStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingProjectSortBy(): void
    {
        $this->resetPage();
    }

    public function updatingDocumentSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDocumentSortBy(): void
    {
        $this->resetPage();
    }

    #[On('project-saved')]
    public function render()
    {
        return view('livewire.clients.show', [
            'projects' => $this->client->projects()
                ->when($this->projectSearch, function ($query) {
                    $query->where('project_name', 'like', '%'.$this->projectSearch.'%');
                })
                ->when($this->projectStatusFilter !== 'all', function ($query) {
                    $query->where('status', $this->projectStatusFilter);
                })
                ->when($this->projectSortBy === 'latest', fn ($query) => $query->latest())
                ->when($this->projectSortBy === 'oldest', fn ($query) => $query->oldest())
                ->when($this->projectSortBy === 'a_to_z', fn ($query) => $query->orderBy('project_name', 'asc'))
                ->when($this->projectSortBy === 'z_to_a', fn ($query) => $query->orderBy('project_name', 'desc'))
                ->paginate(10),
            'documents' => $this->client->clientDocuments()
                ->when($this->documentSearch, function ($query) {
                    $query->where('name', 'like', '%'.$this->documentSearch.'%');
                })
                ->when($this->documentSortBy === 'latest', fn ($query) => $query->latest())
                ->when($this->documentSortBy === 'oldest', fn ($query) => $query->oldest())
                ->when($this->documentSortBy === 'a_to_z', fn ($query) => $query->orderBy('name', 'asc'))
                ->when($this->documentSortBy === 'z_to_a', fn ($query) => $query->orderBy('name', 'desc'))
                ->when($this->documentSortBy === 'largest', fn ($query) => $query->orderBy('size', 'desc'))
                ->when($this->documentSortBy === 'smallest', fn ($query) => $query->orderBy('size', 'asc'))
                ->get(),
        ])->layout('components.layouts.app');
    }
}
