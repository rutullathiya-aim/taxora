<?php

declare(strict_types=1);

namespace App\Livewire\Projects;

use App\Enums\ProjectListStatus;
use App\Enums\ProjectSort;
use App\Filters\ProjectFilters;
use App\Livewire\Base\BaseTableComponent;
use App\Models\Project;
use App\Models\User;
use App\Queries\ProjectQuery;
use App\Services\ProjectManager;
use App\Stats\ProjectStats;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class Index extends BaseTableComponent
{
    use HasProjectActions;

    private ProjectQuery $query;

    private ProjectStats $projectStats;

    protected ProjectManager $projectManager;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: ProjectSort::Latest->value)]
    public string $sortBy = ProjectSort::Latest->value;

    #[Url(except: ProjectListStatus::Active->value)]
    public string $status = ProjectListStatus::Active->value;

    public function boot(ProjectQuery $query, ProjectStats $stats, ProjectManager $projectManager): void
    {
        $this->query = $query;
        $this->projectStats = $stats;
        $this->projectManager = $projectManager;
    }

    public function mount(): void
    {
        parent::mount();

        $this->authorize('viewAny', Project::class);

        if (ProjectSort::tryFrom($this->sortBy) === null) {
            $this->sortBy = ProjectSort::Latest->value;
        }

        if (! self::isValidStatus($this->status)) {
            $this->status = ProjectListStatus::Active->value;
        }
    }

    #[On('projects.saved')]
    public function projectSaved(): void
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
    public function filters(): ProjectFilters
    {
        return new ProjectFilters(
            search: $this->search,
            status: ProjectListStatus::tryFrom($this->status) ?? ProjectListStatus::Active,
            sort: ProjectSort::tryFrom($this->sortBy) ?? ProjectSort::Latest,
            perPage: $this->perPage,
        );
    }

    #[Computed]
    public function projects(): LengthAwarePaginator
    {
        $filters = $this->filters();

        return $this->query->query($this->user(), $filters)->paginate($filters->perPage);
    }

    #[Computed]
    public function stats(): array
    {
        return $this->projectStats->cards($this->user());
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
        return ProjectListStatus::tryFrom($status) !== null;
    }

    public function render(): View
    {
        return view('livewire.projects.index')->layout('components.layouts.app');
    }
}
