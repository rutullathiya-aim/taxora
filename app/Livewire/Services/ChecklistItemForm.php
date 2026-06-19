<?php

namespace App\Livewire\Services;

use App\Enums\ChecklistAction;
use App\Enums\ServiceChecklistItemStatus;
use App\Models\Service;
use App\Models\ServiceChecklistItem;
use App\Services\ServiceManager;
use Flux\Flux;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ChecklistItemForm extends Component
{
    public bool $showItemModal = false;

    public ?ServiceChecklistItem $item = null;

    public ?string $serviceId = null;

    public string $itemTitle = '';

    public ?string $itemDescription = null;

    public bool $itemIsMandatory = true;

    public string $status = '';

    public function boot(): void
    {
        if (empty($this->status)) {
            $this->status = ServiceChecklistItemStatus::Active->value;
        }
    }

    public function rules(): array
    {
        return [
            'itemTitle' => ['required', 'string', 'min:2', 'max:255'],
            'itemDescription' => 'nullable|string|max:5000',
            'itemIsMandatory' => 'boolean',
            'status' => ['required', new Enum(ServiceChecklistItemStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'itemTitle.required' => 'Checklist item title is required.',
            'itemTitle.min' => 'Checklist item title must be at least 2 characters.',

            'itemDescription.max' => 'Description may not exceed 5000 characters.',

        ];
    }

    #[On('create-item')]
    public function openCreateModal(string $serviceId): void
    {
        $service = Service::findOrFail($serviceId);
        $this->authorize('update', $service);

        if ($this->item !== null) {
            $this->resetItemForm();
        }
        $this->resetValidation();
        $this->serviceId = $serviceId;
        $this->showItemModal = true;
    }

    #[On('edit-item')]
    public function openEditModal(string $id): void
    {
        $item = ServiceChecklistItem::findOrFail($id);
        $this->authorize('update', $item->service);

        $this->resetItemForm();

        $this->item = $item;
        $this->serviceId = $item->service_id;
        $this->itemTitle = $item->title;
        $this->itemDescription = $item->description;
        $this->itemIsMandatory = $item->is_mandatory;
        $this->status = $item->status->value;
        $this->showItemModal = true;
    }

    public function save(ServiceManager $serviceManager): void
    {
        $service = Service::findOrFail($this->serviceId);
        $this->authorize('update', $service);

        $this->itemTitle = trim($this->itemTitle);
        $this->itemDescription = $this->itemDescription === null ? null : trim($this->itemDescription);

        $validated = $this->validate();

        $data = [
            'title' => $validated['itemTitle'],
            'description' => $validated['itemDescription'],
            'is_mandatory' => $this->itemIsMandatory,
            'status' => $this->status,
        ];

        if ($this->item) {
            $serviceManager->updateChecklistItem($this->item, $data);
            Flux::toast(ChecklistAction::Updated->message(), variant: 'success');
        } else {
            $serviceManager->createChecklistItem($service, $data);
            Flux::toast(ChecklistAction::Created->message(), variant: 'success');
        }

        $this->resetItemForm();
        $this->showItemModal = false;
        $this->dispatch('item-saved');
    }

    private function resetItemForm(): void
    {
        $this->reset([
            'item',
            'serviceId',
            'itemTitle',
            'itemDescription',
        ]);

        $this->itemIsMandatory = true;
        $this->status = ServiceChecklistItemStatus::Active->value;

        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.services.checklist-item-form');
    }
}
