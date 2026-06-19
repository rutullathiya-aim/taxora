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

        if ($this->item !== null) {
            $serviceManager->updateChecklistItem($this->item, $data);
            Flux::toast(ChecklistAction::Updated->message(), variant: 'success');
        } else {
            $serviceManager->createChecklistItem($service, $data);
            Flux::toast(ChecklistAction::Created->message(), variant: 'success');
        }

        $this->finish();
    }

    private function finish(): void
    {
        $this->showItemModal = false;
        $this->resetItemForm();
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
