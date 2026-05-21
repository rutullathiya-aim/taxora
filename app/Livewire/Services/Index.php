<?php

namespace App\Livewire\Services;

use App\Models\Service;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showCreateModal = false;

    public ?string $editingServiceId = null;

    public string $search = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string')]
    public ?string $description = null;

    #[Validate('required|string|max:50')]
    public string $icon = 'briefcase';

    #[Validate('required|in:active,inactive')]
    public string $status = 'active';

    public function mount(): void {}

    public function createService(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function editService(string $id): void
    {
        $service = Service::findOrFail($id);
        $this->editingServiceId = $service->id;
        $this->name = $service->name;
        $this->description = $service->description;
        $this->icon = $service->icon;
        $this->status = $service->status;
        $this->showCreateModal = true;
    }

    public function saveService(): void
    {

        $rules = [
            'name' => 'required|string|max:255|unique:services,name'.($this->editingServiceId ? ','.$this->editingServiceId : ''),
            'description' => 'nullable|string',
            'icon' => 'required|string|max:50',
            'status' => 'required|in:active,inactive',
        ];

        $validated = $this->validate($rules);
        $validated['slug'] = Str::slug($validated['name']);

        if ($this->editingServiceId) {
            Service::findOrFail($this->editingServiceId)->update($validated);
        } else {
            Service::create($validated);
        }

        $this->resetForm();
        $this->showCreateModal = false;
    }

    public function deleteService(string $id): void
    {
        Service::findOrFail($id)->delete();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'description', 'icon', 'status', 'editingServiceId']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.services.index', [
            'services' => Service::query()
                ->withCount('checklistItems')
                ->when($this->search, fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
                ->latest()
                ->paginate(12),
            'totalServices' => Service::count(),
            'activeServices' => Service::where('status', 'active')->count(),
            'inactiveServices' => Service::where('status', 'inactive')->count(),
        ])->layout('components.layouts.app');
    }
}
