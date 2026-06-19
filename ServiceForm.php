            'status.enum' => 'Selected Status is invalid.',
        ];
    }

    #[On('create-service')]
    public function openCreateModal(): void
    {
        $this->authorize('create', Service::class);
        if ($this->service !== null) {
            $this->resetForm();
        }
        $this->resetValidation();
        $this->showModal = true;
    }

    #[On('edit-service')]
    public function openEditModal(string $id): void
    {
        $service = Service::query()->findOrFail($id);
        $this->authorize('update', $service);

        $this->resetForm();
        $this->fillFromModel($service);
        $this->showModal = true;
    }

    public function save(ServiceManager $manager): void
    {
        $this->authorizeSave();
        $this->sanitize();
        $validated = $this->validate();
        $serviceData = $this->serviceData($validated);

        if ($this->service !== null) {
            $this->updateService($manager, $serviceData);

            return;
        }

        $this->storeService($manager, $serviceData);
    }

    private function authorizeSave(): void
    {
        if ($this->service) {
            $this->authorize('update', $this->service);

            return;
        }
