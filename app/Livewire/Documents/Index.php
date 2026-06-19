<?php

namespace App\Livewire\Documents;

use App\Enums\DocumentAction;
use App\Enums\DocumentSort;
use App\Enums\ListFilter;
use App\Livewire\Base\BaseTableComponent;
use App\Models\Client;
use App\Models\Document;
use App\Queries\DocumentQueries;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

class Index extends BaseTableComponent
{
    use HasDocumentActions;
    use HasDocumentUploads;
    use WithFileUploads;

    protected function getPageResetProperties(): array
    {
        return [
            'search',
            'sortBy',
            'status',
        ];
    }

    public Client $client;

    #[Url(as: 'documentSearch', except: '')]
    public string $search = '';

    #[Url(as: 'documentSortBy', except: DocumentSort::Latest->value)]
    public string $sortBy = DocumentSort::Latest->value;

    #[Url(as: 'documentStatus', except: 'active')]
    public string $status = 'active';

    public function getDocument(string $id): Document
    {
        $document = Document::withTrashed()->findOrFail($id);

        $this->authorize('view', $document);

        return $document;
    }

    public function mount(Client $client): void
    {
        $this->perPage = auth()->user()?->getPreference('per_page', 10) ?? 10;
        $this->authorize('view', $client);
        $this->client = $client;

        if (! self::isValidSort($this->sortBy)) {
            $this->sortBy = DocumentSort::Latest->value;
        }

        if (! self::isValidStatus($this->status)) {
            $this->status = 'active';
        }
    }

    #[On('document-updated')]
    public function documentUpdated(): void
    {
        // Empty to trigger Livewire re-render
    }

    public function setStatusFilter(string $status): void
    {
        if (! self::isValidStatus($status)) {
            return;
        }

        $this->status = $status;
        $this->resetPage();
    }

    #[Computed]
    public function documents(): LengthAwarePaginator
    {
        return $this->client->documents()
            ->select(['id', 'client_id', 'name', 'path', 'mime_type', 'size', 'created_by', 'created_at', 'deleted_at'])
            ->filterStatus($this->status)
            ->search($this->search)
            ->sorted(DocumentSort::tryFrom($this->sortBy) ?? DocumentSort::Latest)
            ->paginate($this->perPage);
    }

    #[Computed]
    public function stats(): array
    {
        return DocumentQueries::stats(
            $this->client->documents()->withTrashed()
        );
    }

    private static function isValidSort(string $sort): bool
    {
        return DocumentSort::tryFrom($sort) !== null;
    }

    private static ?array $allowedStatuses = null;

    private static function allowedStatuses(): array
    {
        return self::$allowedStatuses ??= [
            'active',
            ListFilter::Deleted->value,
            ListFilter::All->value,
        ];
    }

    public function afterDocumentAction(DocumentAction $action, Document $document): void
    {
        $this->dispatch('document-updated');
    }

    private static function isValidStatus(string $status): bool
    {
        return in_array($status, self::allowedStatuses(), true);
    }

    public function render(): View
    {
        return view('livewire.documents.index');
    }
}
