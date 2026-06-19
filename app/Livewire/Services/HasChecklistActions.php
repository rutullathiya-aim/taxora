<?php

namespace App\Livewire\Services;

use App\Enums\ChecklistAction;
use App\Enums\ServiceChecklistItemStatus;
use App\Models\ServiceChecklistItem;
use App\Services\ServiceManager;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;

trait HasChecklistActions
{
    use AuthorizesRequests;

    private function getAuthorizedItem(string $id): ServiceChecklistItem
    {
        $this->authorize('update', $this->service);

        return $this->service->checklistItems()->findOrFail($id);
    }

    #[On('delete-item')]
    public function deleteItem(string $id, ServiceManager $serviceManager): void
    {
        $item = $this->getAuthorizedItem($id);
        $serviceManager->deleteChecklistItem($item);

        $this->reindexSortOrder($serviceManager);
        $this->service->load('checklistItems');
        Flux::toast(ChecklistAction::Deleted->message(), variant: 'success');
    }

    public function duplicateItem(string $id, ServiceManager $serviceManager): void
    {
        $item = $this->getAuthorizedItem($id);

        $newItemData = $item->toArray();
        $newItemData['title'] = str($item->title)->limit(245, '')->append(' (Copy)');

        $serviceManager->createChecklistItem($this->service, $newItemData);

        $this->service->load('checklistItems');
        Flux::toast(ChecklistAction::Created->message(), variant: 'success');
    }

    public function toggleMandatory(string $id, ServiceManager $serviceManager): void
    {
        $item = $this->getAuthorizedItem($id);
        $serviceManager->updateChecklistItem($item, ['is_mandatory' => ! $item->is_mandatory]);
        $this->service->load('checklistItems');
        Flux::toast(ChecklistAction::Updated->message(), variant: 'success');
    }

    public function toggleActive(string $id, ServiceManager $serviceManager): void
    {
        $item = $this->getAuthorizedItem($id);
        $newStatus = $item->status === ServiceChecklistItemStatus::Active
            ? ServiceChecklistItemStatus::Inactive->value
            : ServiceChecklistItemStatus::Active->value;

        $serviceManager->updateChecklistItem($item, ['status' => $newStatus]);
        $this->service->load('checklistItems');
        Flux::toast(ChecklistAction::Updated->message(), variant: 'success');
    }

    public function updateItemOrder(string $itemId, int $newIndex, ServiceManager $serviceManager): void
    {
        $item = $this->getAuthorizedItem($itemId);
        $allItems = $this->service->checklistItems()->orderBy('sort_order')->get();

        $newIndex = max(0, $newIndex);
        abort_if($newIndex > $allItems->count(), 422);

        $remainingItems = $allItems->reject(fn ($i) => $i->is($item))->values();
        $remainingItems->splice($newIndex, 0, [$item]);

        $serviceManager->reorderChecklistItems($remainingItems->pluck('id')->toArray());

        $this->service->load('checklistItems');
    }

    private function reindexSortOrder(ServiceManager $serviceManager): void
    {
        $items = $this->service->checklistItems()->orderBy('sort_order')->get();

        $serviceManager->reorderChecklistItems($items->pluck('id')->toArray());
    }
}
