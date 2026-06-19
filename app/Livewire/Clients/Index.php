<?php

declare(strict_types=1);

namespace App\Livewire\Clients;

use App\Enums\ClientListStatus;
use App\Enums\ClientSort;
use App\Filters\ClientFilters;
use App\Livewire\Base\BaseTableComponent;
use App\Models\Client;
use App\Models\User;
use App\Queries\ClientQuery;
use App\Services\ClientManager;
use App\Stats\ClientStats;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class Index extends BaseTableComponent
{
    use HasClientActions;

    private ClientQuery $query;

    private ClientStats $clientStats;

    protected ClientManager $clientManager;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: ClientSort::Latest->value)]
    public string $sortBy = ClientSort::Latest->value;

    #[Url(except: ClientListStatus::Active->value)]
    public string $status = ClientListStatus::Active->value;

    public function boot(ClientQuery $query, ClientStats $stats, ClientManager $clientManager): void
    {
        $this->query = $query;
        $this->clientStats = $stats;
        $this->clientManager = $clientManager;
    }

    public function mount(): void
    {
        parent::mount();

        $this->authorize('viewAny', Client::class);

        if (ClientSort::tryFrom($this->sortBy) === null) {
            $this->sortBy = ClientSort::Latest->value;
        }

        if (! self::isValidStatus($this->status)) {
            $this->status = ClientListStatus::Active->value;
        }
    }

    #[On('clients.saved')]
    public function clientSaved(): void
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
    public function filters(): ClientFilters
    {
        return new ClientFilters(
            search: $this->search,
            status: ClientListStatus::tryFrom($this->status) ?? ClientListStatus::Active,
            sort: ClientSort::tryFrom($this->sortBy) ?? ClientSort::Latest,
            perPage: $this->perPage,
        );
    }

    #[Computed]
    public function clients(): LengthAwarePaginator
    {
        $filters = $this->filters();

        return $this->query->query($this->user(), $filters)->paginate($filters->perPage);
    }

    #[Computed]
    public function stats(): array
    {
        return $this->clientStats->cards($this->user());
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
        return ClientListStatus::tryFrom($status) !== null;
    }

    public function render(): View
    {
        return view('livewire.clients.index')->layout('components.layouts.app');
    }
}
