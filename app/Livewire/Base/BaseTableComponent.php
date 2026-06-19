<?php

declare(strict_types=1);

namespace App\Livewire\Base;

use Livewire\Component;
use Livewire\WithPagination;

abstract class BaseTableComponent extends Component
{
    use WithPagination;

    public int $perPage = 10;

    /**
     * @return array<int, string>
     */
    abstract protected function getPageResetProperties(): array;

    public function mount(): void
    {
        $defaultPerPage = (int) config('taxora.pagination.default', 10);
        $options = config('taxora.pagination.options', [10, 25, 50, 100]);

        $preference = (int) (auth()->user()?->getPreference('per_page', $defaultPerPage) ?? $defaultPerPage);

        $this->perPage = in_array($preference, $options, true) ? $preference : $defaultPerPage;
    }

    public function updated(string $property): void
    {
        if (in_array($property, $this->getPageResetProperties(), true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(...$this->getPageResetProperties());
        $this->resetPage();
    }

    public function updatedPerPage(mixed $value): void
    {
        $value = (int) $value;
        $options = config('taxora.pagination.options', [10, 25, 50, 100]);

        if (! in_array($value, $options, true)) {
            return;
        }

        $this->perPage = $value;
        auth()->user()?->setPreference('per_page', $value);
        $this->resetPage();
    }
}
