<?php

namespace App\Models;

use App\Enums\ListFilter;
use App\Enums\TaskPriority;
use App\Enums\TaskSort;
use App\Enums\TaskStatus;
use Database\Factories\TaskFactory;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $task_number
 * @property string $title
 * @property string|null $description
 * @property TaskStatus $status
 * @property TaskPriority $priority
 * @property Carbon|null $due_at
 * @property string|null $client_id
 * @property string|null $project_id
 * @property string $created_by
 * @property Carbon|null $completed_at
 * @property string|null $completed_by
 * @property-read bool $is_overdue
 * @property-read User|null $createdBy
 * @property-read User|null $completedBy
 * @property-read Client|null $client
 * @property-read Project|null $project
 * @property-read Collection<int, User> $assignees
 * @property-read Collection<int, TaskActivity> $activities
 * @property-read Collection<int, TaskComment> $comments
 */
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_at',
        'client_id',
        'project_id',
        'created_by',
        'completed_at',
        'completed_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if (empty($task->task_number)) {
                $task->task_number = static::generateTaskNumber();
            }
        });
    }

    /**
     * Generate a sequential task number using an atomic database lock.
     */
    public static function generateTaskNumber(): string
    {
        return DB::transaction(function () {
            $sequence = DB::table('task_sequences')->lockForUpdate()->first();
            $nextNumber = $sequence->last_number + 1;

            DB::table('task_sequences')
                ->where('id', $sequence->id)
                ->update(['last_number' => $nextNumber]);

            return 'TASK-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    protected function displayStatus(): TaskStatus|ListFilter
    {
        return $this->trashed() ? ListFilter::Deleted : $this->status;
    }

    public function statusLabel(): string
    {
        return $this->displayStatus()->label();
    }

    public function statusColor(): string
    {
        return $this->displayStatus()->color();
    }

    public function priorityLabel(): string
    {
        return $this->priority->label();
    }

    public function priorityColor(): string
    {
        return $this->priority->color();
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->due_at) {
                return false;
            }

            return $this->due_at->isPast()
                && ! in_array($this->status, [TaskStatus::Completed, TaskStatus::Cancelled], true);
        });
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TaskActivity::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdminOrManager()) {
            return $query;
        }

        return $query->whereRelation('assignees', 'users.id', $user->getKey());
    }

    public function scopeStatus(Builder $query, TaskStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeFilterStatus(Builder $query, string $filter): Builder
    {
        if ($status = TaskStatus::tryFrom($filter)) {
            return $query->status($status);
        }

        return match ($filter) {
            ListFilter::Deleted->value => $query->onlyTrashed(),
            default => $query,
        };
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = Str::of((string) $search)->squish();

        if ($search->isEmpty()) {
            return $query;
        }

        $searchPattern = "%{$search}%";

        return $query->where(function (Builder $sub) use ($searchPattern) {
            $sub->where('task_number', 'like', $searchPattern)
                ->orWhere('title', 'like', $searchPattern)
                ->orWhere('description', 'like', $searchPattern)
                ->orWhereHas('client', fn (Builder $q) => $q->where('client_name', 'like', $searchPattern)->orWhere('company_name', 'like', $searchPattern))
                ->orWhereHas('project', fn (Builder $q) => $q->where('project_name', 'like', $searchPattern));
        });
    }

    public function scopeSorted(Builder $query, TaskSort $sort): Builder
    {
        return match ($sort) {
            TaskSort::Latest => $query->orderByDesc('created_at')->orderByDesc('id'),
            TaskSort::Oldest => $query->orderBy('created_at')->orderBy('id'),
            TaskSort::TitleAsc => $query->orderBy('title', 'asc')->orderBy('id'),
            TaskSort::TitleDesc => $query->orderBy('title', 'desc')->orderBy('id'),
            TaskSort::DueAsc => $query->orderBy('due_at', 'asc')->orderBy('id'),
            TaskSort::DueDesc => $query->orderByDesc('due_at')->orderByDesc('id'),
        };
    }
}
