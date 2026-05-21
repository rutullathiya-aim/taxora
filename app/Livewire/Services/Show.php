<?php

namespace App\Livewire\Services;

use App\Models\Service;
use App\Models\ServiceChecklistItem;
use Illuminate\Support\Str;
use Livewire\Component;

class Show extends Component
{
    public Service $service;

    public string $activeTab = 'checklists';

    // Search and Filters
    public string $search = '';

    public string $filterMandatory = '';

    public string $filterStatus = '';

    // Checklist Item CRUD fields
    public bool $showItemModal = false;

    public ?string $editingItemId = null;

    public string $itemTitle = '';

    public ?string $itemDescription = null;

    public bool $itemIsMandatory = true;

    public ?string $itemAllowedFileTypes = 'pdf,jpg,png';

    public string $itemStatus = 'active';

    // Settings fields
    public string $settingsName = '';

    public ?string $settingsDescription = null;

    public string $settingsIcon = '';

    public string $settingsStatus = 'active';

    public function mount(Service $service): void
    {
        $this->service = $service->load('checklistItems');

        // Initialize settings fields
        $this->settingsName = $service->name;
        $this->settingsDescription = $service->description;
        $this->settingsIcon = $service->icon ?? '';
        $this->settingsStatus = $service->status;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ─── Checklist Item CRUD ──────────────────────────────────────────

    public function createItem(): void
    {
        $this->resetItemForm();
        $this->showItemModal = true;
    }

    public function editItem(string $id): void
    {
        $item = ServiceChecklistItem::findOrFail($id);
        abort_if($item->service_id !== $this->service->id, 403);

        $this->editingItemId = $item->id;
        $this->itemTitle = $item->title;
        $this->itemDescription = $item->description;
        $this->itemIsMandatory = $item->is_mandatory;
        $this->itemAllowedFileTypes = $item->allowed_file_types;
        $this->itemStatus = $item->status;
        $this->showItemModal = true;
    }

    public function saveItem(): void
    {

        $validated = $this->validate([
            'itemTitle' => 'required|string',
            'itemDescription' => 'nullable|string',
            'itemIsMandatory' => 'boolean',
            'itemAllowedFileTypes' => 'nullable|string|max:255',
            'itemStatus' => 'required|in:active,inactive',
        ]);

        $data = [
            'title' => $validated['itemTitle'],
            'description' => $validated['itemDescription'],
            'is_mandatory' => $validated['itemIsMandatory'],
            'allowed_file_types' => $validated['itemAllowedFileTypes'] ?: null,
            'status' => $validated['itemStatus'],
        ];

        if ($this->editingItemId) {
            $item = ServiceChecklistItem::findOrFail($this->editingItemId);
            abort_if($item->service_id !== $this->service->id, 403);
            $item->update($data);
        } else {
            $maxSort = $this->service->checklistItems()->max('sort_order') ?? -1;
            $data['service_id'] = $this->service->id;
            $data['sort_order'] = $maxSort + 1;
            ServiceChecklistItem::create($data);
        }

        $this->resetItemForm();
        $this->showItemModal = false;
        $this->service->refresh()->load('checklistItems');
    }

    public function deleteItem(string $id): void
    {

        $item = ServiceChecklistItem::findOrFail($id);
        abort_if($item->service_id !== $this->service->id, 403);

        $item->delete();
        $this->reindexSortOrder();
        $this->service->refresh()->load('checklistItems');
    }

    public function duplicateItem(string $id): void
    {

        $item = ServiceChecklistItem::findOrFail($id);
        abort_if($item->service_id !== $this->service->id, 403);

        $maxSort = $this->service->checklistItems()->max('sort_order') ?? -1;
        $newItem = $item->replicate();
        $newItem->title = $item->title.' (Copy)';
        $newItem->sort_order = $maxSort + 1;
        $newItem->save();

        $this->service->refresh()->load('checklistItems');
    }

    public function toggleMandatory(string $id): void
    {

        $item = ServiceChecklistItem::findOrFail($id);
        abort_if($item->service_id !== $this->service->id, 403);

        $item->update(['is_mandatory' => ! $item->is_mandatory]);
        $this->service->refresh()->load('checklistItems');
    }

    public function toggleStatus(string $id): void
    {

        $item = ServiceChecklistItem::findOrFail($id);
        abort_if($item->service_id !== $this->service->id, 403);

        $newStatus = $item->status === 'active' ? 'inactive' : 'active';
        $item->update(['status' => $newStatus]);
        $this->service->refresh()->load('checklistItems');
    }

    // ─── Reordering Logic ─────────────────────────────────────────────

    public function updateItemOrder(string $itemId, int $newIndex): void
    {
        $item = ServiceChecklistItem::findOrFail($itemId);
        abort_if($item->service_id !== $this->service->id, 403);

        // 1. Fetch all items for this service, ordered by sort order
        $allItems = $this->service->checklistItems()->orderBy('sort_order')->get();

        // 2. Exclude the current item
        $remainingItems = $allItems->reject(fn ($i) => $i->id === $itemId)->values();

        // 3. Insert dragged item at exact newIndex position
        $remainingItems->splice($newIndex, 0, [$item]);

        // 4. Update DB sort orders
        foreach ($remainingItems->values() as $index => $i) {
            $i->update(['sort_order' => $index]);
        }

        $this->service->refresh()->load('checklistItems');
    }

    private function reindexSortOrder(): void
    {
        $items = $this->service->checklistItems()->orderBy('sort_order')->get();
        foreach ($items->values() as $index => $item) {
            $item->update(['sort_order' => $index]);
        }
    }

    public function resetItemForm(): void
    {
        $this->reset(['itemTitle', 'itemDescription', 'itemIsMandatory', 'itemAllowedFileTypes', 'itemStatus', 'editingItemId']);
        $this->itemIsMandatory = true;
        $this->itemStatus = 'active';
        $this->itemAllowedFileTypes = 'pdf,jpg,png';
        $this->resetValidation();
    }

    // ─── Settings ─────────────────────────────────────────────────────

    public function saveSettings(): void
    {

        $validated = $this->validate([
            'settingsName' => 'required|string|max:255|unique:services,name,'.$this->service->id,
            'settingsDescription' => 'nullable|string',
            'settingsIcon' => 'required|string|max:50',
            'settingsStatus' => 'required|in:active,inactive',
        ]);

        $this->service->update([
            'name' => $validated['settingsName'],
            'slug' => Str::slug($validated['settingsName']),
            'description' => $validated['settingsDescription'],
            'icon' => $validated['settingsIcon'],
            'status' => $validated['settingsStatus'],
        ]);

        $this->service->refresh();
    }

    public function render()
    {
        // Apply search and filter constraints
        $itemsQuery = $this->service->checklistItems()
            ->when($this->search, fn ($query) => $query->where('title', 'like', '%'.$this->search.'%'))
            ->when($this->filterMandatory !== '', function ($query) {
                return $query->where('is_mandatory', $this->filterMandatory === 'yes');
            })
            ->when($this->filterStatus, fn ($query) => $query->where('status', $this->filterStatus))
            ->orderBy('sort_order');

        $items = $itemsQuery->get();

        return view('livewire.services.show', [
            'items' => $items,
            'totalItems' => $this->service->checklistItems()->count(),
            'mandatoryItemsCount' => $this->service->checklistItems()->where('is_mandatory', true)->count(),
            'activeItemsCount' => $this->service->checklistItems()->where('status', 'active')->count(),
        ])->layout('components.layouts.app');
    }
}
