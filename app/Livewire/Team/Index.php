<?php

declare(strict_types=1);

namespace App\Livewire\Team;

use App\Enums\UserListRole;
use App\Enums\UserListStatus;
use App\Enums\UserSort;
use App\Filters\UserFilters;
use App\Livewire\Base\BaseTableComponent;
use App\Models\User;
use App\Queries\UserQuery;
use App\Services\TeamManager;
use App\Stats\TeamStats;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class Index extends BaseTableComponent
{
    use HasTeamActions;

    private UserQuery $query;

    private TeamStats $teamStats;

    protected TeamManager $teamManager;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: UserListRole::All->value)]
    public string $role = UserListRole::All->value;

    #[Url(except: UserSort::Latest->value)]
    public string $sortBy = UserSort::Latest->value;

    #[Url(except: UserListStatus::All->value)]
    public string $status = UserListStatus::All->value;

    public function boot(UserQuery $query, TeamStats $stats, TeamManager $teamManager): void
    {
        $this->query = $query;
        $this->teamStats = $stats;
        $this->teamManager = $teamManager;
    }

    public function mount(): void
    {
        parent::mount();

        $this->authorize('viewAny', User::class);

        if (UserListRole::tryFrom($this->role) === null) {
            $this->role = UserListRole::All->value;
        }

        if (UserSort::tryFrom($this->sortBy) === null) {
            $this->sortBy = UserSort::Latest->value;
        }

        if (! self::isValidStatus($this->status)) {
            $this->status = UserListStatus::All->value;
        }
    }

    #[On('teams.saved')]
    public function teamMemberSaved(): void
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
    public function filters(): UserFilters
    {
        return new UserFilters(
            search: $this->search,
            status: UserListStatus::tryFrom($this->status) ?? UserListStatus::All,
            sort: UserSort::tryFrom($this->sortBy) ?? UserSort::Latest,
            role: UserListRole::tryFrom($this->role) ?? UserListRole::All,
            perPage: $this->perPage,
        );
    }

    #[Computed]
    public function users(): LengthAwarePaginator
    {
        $filters = $this->filters();

        return $this->query->query($filters)->paginate($filters->perPage);
    }

    #[Computed]
    public function stats(): array
    {
        return $this->teamStats->cards();
    }

    protected function getPageResetProperties(): array
    {
        return [
            'search',
            'role',
            'sortBy',
            'status',
        ];
    }

    private static function isValidStatus(string $status): bool
    {
        return UserListStatus::tryFrom($status) !== null;
    }

    public function render(): View
    {
        return view('livewire.team.index')->layout('components.layouts.app');
    }
}
