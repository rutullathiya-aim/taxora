<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChecklistStatus;
use App\Enums\ProjectListStatus;
use App\Enums\ProjectStatus;
use App\Events\Project\ForceDeleted;
use App\Services\ProjectCodeGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $project_code
 * @property string $client_id
 * @property string $project_name
 * @property string|null $description
 * @property string $service_id
 * @property ProjectStatus $status
 * @property Carbon|null $due_date
 * @property string|null $created_by
 * @property-read Client|null $client
 * @property-read Service|null $service
 * @property-read User|null $createdBy
 * @property-read Collection<int, User> $assignees
 * @property-read Collection<int, ProjectChecklist> $checklists
 * @property-read Collection<int, Task> $tasks
 * @property-read int|null $total_checklists
 * @property-read int|null $completed_checklists
 * @property-read int $progress
 */
class Project extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $dispatchesEvents = [
        'forceDeleted' => ForceDeleted::class,
    ];

    protected $fillable = [
        'client_id',
        'project_name',
        'description',
        'service_id',
        'status',
        'due_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'due_date' => 'date',
        ];
    }

    public function isCompleted(): bool
    {
        return $this->status === ProjectStatus::Completed;
    }

    public function isOverdue(): bool
    {
        return $this->due_date?->isPast() && ! $this->isCompleted();
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * @return HasMany<ProjectChecklist, $this>
     */
    public function checklists(): HasMany
    {
        return $this->hasMany(ProjectChecklist::class);
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    private static function calculateProgress(int $completed, int $total): int
    {
        if ($total === 0) {
            return 0;
        }

        return max(
            0,
            min(100, (int) round(($completed / $total) * 100))
        );
    }

    protected function progress(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (array_key_exists('total_checklists', $this->attributes)) {
                    $total = (int) $this->total_checklists;
                    $completed = (int) $this->completed_checklists;

                    return self::calculateProgress($completed, $total);
                }

                $checklists = $this->checklists;

                $total = $checklists->count();
                $completed = $checklists
                    ->whereIn('status', ChecklistStatus::completionStatuses())
                    ->count();

                return self::calculateProgress($completed, $total);
            }
        );
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdminOrManager()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->whereRelation('assignees', 'users.id', $user->getKey())
                ->orWhereRelation('tasks.assignees', 'users.id', $user->getKey());
        })->whereNull($this->getTable() . '.deleted_at');
    }

    public function scopeStatus(Builder $query, ProjectStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeFilterStatus(Builder $query, ProjectListStatus $status): Builder
    {
        return match ($status) {
            ProjectListStatus::All => $query,
            ProjectListStatus::Active => $query->status(ProjectStatus::Active),
            ProjectListStatus::Completed => $query->status(ProjectStatus::Completed),
            ProjectListStatus::OnHold => $query->status(ProjectStatus::OnHold),
            ProjectListStatus::Cancelled => $query->status(ProjectStatus::Cancelled),
            ProjectListStatus::Deleted => $query->onlyTrashed(),
        };
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = Str::of((string) $search)->trim()->squish();

        if ($search->isEmpty()) {
            return $query;
        }

        $searchPattern = "%{$search}%";

        return $query->where(function (Builder $sub) use ($searchPattern) {
            $sub->where('project_name', 'like', $searchPattern)
                ->orWhere('project_code', 'like', $searchPattern)
                ->orWhereHas('client', fn ($q) => $q->where('client_name', 'like', $searchPattern))
                ->orWhereHas('service', fn ($q) => $q->where('name', 'like', $searchPattern));
        });
    }

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (blank($project->project_code)) {
                $project->project_code = ProjectCodeGenerator::next();
            }
        });
    }
}
