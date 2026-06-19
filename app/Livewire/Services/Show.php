<?php

namespace App\Livewire\Services;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class Show extends Component
{
    use HasChecklistActions, HasServiceActions;

    private const VALID_BOOLEAN_FILTERS = [
        '',
        'yes',
        'no',
    ];

    public Service $service;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $filterMandatory = '';

    #[Url(except: '')]
    public string $filterActive = '';

    public function mount(Service $service): void
    {
        $this->authorize('view', $service);
        $this->service = $service->load('checklistItems');

        if (! in_array($this->filterMandatory, self::VALID_BOOLEAN_FILTERS, true)) {
            $this->filterMandatory = '';
        }

        if (! in_array($this->filterActive, self::VALID_BOOLEAN_FILTERS, true)) {
            $this->filterActive = '';
        }
    }

    public function updated(string $property): void
    {
        if ($property === 'filterMandatory' && ! in_array($this->filterMandatory, self::VALID_BOOLEAN_FILTERS, true)) {
            $this->filterMandatory = '';
        }

        if ($property === 'filterActive' && ! in_array($this->filterActive, self::VALID_BOOLEAN_FILTERS, true)) {
            $this->filterActive = '';
        }
    }

    #[On('services.saved')]
    #[On('item-saved')]
    public function refreshService(): void
    {
        $this->service->refresh();
        $this->dispatch('update-heading', $this->service->name);
    }

    public function afterServiceAction(string $action, Service $service): void
    {
        if ($action === 'forceDelete' && $this->service->id === $service->id) {
            $this->redirect(route('services.index'), navigate: true);

            return;
        }

        if (in_array($action, ['delete', 'restore']) && $this->service->id === $service->id) {
            $this->service->refresh();
        }
    }

    #[Computed]
    public function stats(): array
    {
        $all = $this->service->checklistItems;

        return [
            'total' => $all->count(),
            'mandatory' => $all->where('is_mandatory', true)->count(),
            'active' => $all->where('is_active', true)->count(),
        ];
    }

    #[Computed]
    public function items(): Collection
    {
        $query = $this->itemQuery();

        $this->applySearch($query);
        $this->applyMandatoryFilter($query);
        $this->applyActiveFilter($query);
        $this->applySorting($query);

        return $query->get();
    }

    protected function itemQuery(): HasMany
    {
        return $this->service->checklistItems();
    }

    protected function applySearch(HasMany $query): void
    {
        $search = trim($this->search);

        if (blank($search)) {
            return;
        }

        $query->where('title', 'like', '%' . $search . '%');
    }

    protected function applyMandatoryFilter(HasMany $query): void
    {
        if ($this->filterMandatory === '') {
            return;
        }

        $query->where('is_mandatory', $this->filterMandatory === 'yes');
    }

    protected function applyActiveFilter(HasMany $query): void
    {
        if ($this->filterActive === '') {
            return;
        }

        $query->where('is_active', $this->filterActive === 'yes');
    }

    protected function applySorting(HasMany $query): void
    {
        $query->orderBy('sort_order');
    }

    public function render()
    {
        return view('livewire.services.show')->layout('components.layouts.app');
    }
}
