<?php

declare(strict_types=1);

namespace App\Livewire\Services;

use App\Enums\ServiceListStatus;
use App\Enums\ServiceSort;
use App\Filters\ServiceFilters;
use App\Livewire\Base\BaseTableComponent;
use App\Models\Service;
use App\Models\User;
use App\Queries\ServiceQuery;
use App\Services\ServiceManager;
use App\Stats\ServiceStats;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class Index extends BaseTableComponent
{
    use HasServiceActions;

    private ServiceQuery $query;

    private ServiceStats $serviceStats;

    protected ServiceManager $serviceManager;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: ServiceSort::Latest->value)]
    public string $sortBy = ServiceSort::Latest->value;

    #[Url(except: ServiceListStatus::Active->value)]
    public string $status = ServiceListStatus::Active->value;

    public function boot(ServiceQuery $query, ServiceStats $stats, ServiceManager $serviceManager): void
    {
        $this->query = $query;
        $this->serviceStats = $stats;
        $this->serviceManager = $serviceManager;
    }

    public function mount(): void
    {
        parent::mount();

        $this->authorize('viewAny', Service::class);

        if (ServiceSort::tryFrom($this->sortBy) === null) {
            $this->sortBy = ServiceSort::Latest->value;
        }

        if (! self::isValidStatus($this->status)) {
            $this->status = ServiceListStatus::Active->value;
        }
    }

    #[On('services.saved')]
    public function serviceSaved(): void
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
    public function user(): User
    {
        return auth()->user();
    }

    #[Computed]
    public function filters(): ServiceFilters
    {
        return new ServiceFilters(
            search: $this->search,
            status: ServiceListStatus::tryFrom($this->status) ?? ServiceListStatus::Active,
            sort: ServiceSort::tryFrom($this->sortBy) ?? ServiceSort::Latest,
            perPage: $this->perPage,
        );
    }

    #[Computed]
    public function services(): LengthAwarePaginator
    {
        $filters = $this->filters();

        return $this->query->query($this->user(), $filters)->withCount('checklistItems')->paginate($filters->perPage);
    }

    #[Computed]
    public function stats(): array
    {
        return $this->serviceStats->cards($this->user());
    }

    protected function getPageResetProperties(): array
    {
        return [
            'search',
            'sortBy',
            'status',
        ];
    }

    private static function isValidStatus(string $status): bool
    {
        return ServiceListStatus::tryFrom($status) !== null;
    }

    public function render(): View
    {
        return view('livewire.services.index')->layout('components.layouts.app');
    }
}
