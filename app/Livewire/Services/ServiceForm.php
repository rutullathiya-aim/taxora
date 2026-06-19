<?php

declare(strict_types=1);

namespace App\Livewire\Services;

use App\Enums\CrudAction;
use App\Enums\ResourceType;
use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Services\ServiceManager;
use App\Support\Toast;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ServiceForm extends Component
{
    public bool $showModal = false;

    public ?Service $service = null;

    public string $name = '';

    public ?string $description = null;

    public string $status = ServiceStatus::Active->value;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:150', Rule::unique('services', 'name')->ignore($this->service?->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(ServiceStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Service Name is required.',
            'name.min' => 'Service Name must be at least 2 characters.',
            'name.max' => 'Service Name cannot exceed 150 characters.',
            'name.unique' => 'This Service Name is already registered.',
            'description.string' => 'Service Description must be a string.',
            'description.max' => 'Service Description cannot exceed 5000 characters.',
            'status.required' => 'Status is required.',
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

        $this->authorize('create', Service::class);
    }

    private function updateService(ServiceManager $manager, array $serviceData): void
    {
        $manager->update($this->service, $serviceData);
        Toast::success(CrudAction::Updated, ResourceType::Service);
        $this->finish();
    }

    private function storeService(ServiceManager $manager, array $serviceData): void
    {
        $manager->create($serviceData);
        Toast::success(CrudAction::Created, ResourceType::Service);
        $this->finish();
    }

    public function resetForm(): void
    {
        $this->resetDefaults();
        $this->resetValidation();
    }

    private function fillFromModel(Service $service): void
    {
        $this->service = $service;
        $this->name = $service->name;
        $this->description = $service->description;
        $this->status = $service->status->value;
    }

    private function finish(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('services.saved');
    }

    private function resetDefaults(): void
    {
        $this->reset([
            'name',
            'description',
            'service',
        ]);

        $this->status = ServiceStatus::Active->value;
    }

    private function sanitize(): void
    {
        $this->name = trim($this->name);
        $this->description = $this->description === null ? null : trim($this->description);
    }

    private function serviceData(array $validated): array
    {
        return Arr::only($validated, [
            'name',
            'description',
            'status',
        ]);
    }

    public function render(): View
    {
        return view('livewire.services.service-form');
    }
}
