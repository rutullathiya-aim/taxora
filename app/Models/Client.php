<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ClientListStatus;
use App\Enums\ClientStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $client_name
 * @property string|null $company_name
 * @property string $email
 * @property string $phone
 * @property string|null $address
 * @property ClientStatus $status
 * @property string|null $created_by
 * @property-read User|null $createdBy
 * @property-read Collection<int, Project> $projects
 * @property-read Collection<int, Document> $documents
 * @property-read Collection<int, Task> $tasks
 */
class Client extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    private const DOCUMENT_DIRECTORY = 'client-documents';

    protected $fillable = [
        'client_name',
        'company_name',
        'email',
        'phone',
        'address',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ClientStatus::class,
        ];
    }

    public function documentsDirectory(): string
    {
        return self::DOCUMENT_DIRECTORY . '/' . $this->id;
    }

    public function listStatus(): ClientListStatus
    {
        return ClientListStatus::fromState($this->status, $this->trashed());
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * @return HasMany<Document, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdminOrManager()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->whereExists(fn (QueryBuilder $sub) => $this->projectAssignmentExists($sub, $user))
                ->orWhereExists(fn (QueryBuilder $sub) => $this->taskAssignmentExists($sub, $user));
        });
    }

    private function projectAssignmentExists(QueryBuilder $sub, User $user): void
    {
        $sub->selectRaw('1')
            ->from('projects')
            ->join('project_user', 'projects.id', '=', 'project_user.project_id')
            ->whereColumn('projects.client_id', 'clients.id')
            ->where('project_user.user_id', $user->getKey());
    }

    private function taskAssignmentExists(QueryBuilder $sub, User $user): void
    {
        $sub->selectRaw('1')
            ->from('tasks')
            ->join('task_user', 'tasks.id', '=', 'task_user.task_id')
            ->whereColumn('tasks.client_id', 'clients.id')
            ->where('task_user.user_id', $user->getKey());
    }

    public function scopeStatus(Builder $query, ClientStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeFilterStatus(Builder $query, ClientListStatus $status): Builder
    {
        return match ($status) {
            ClientListStatus::Active => $query->status(ClientStatus::Active),
            ClientListStatus::Inactive => $query->status(ClientStatus::Inactive),
            ClientListStatus::Deleted => $query->onlyTrashed(),
            ClientListStatus::All => $query,
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
            $sub->where('client_name', 'like', $searchPattern)
                ->orWhere('company_name', 'like', $searchPattern)
                ->orWhere('email', 'like', $searchPattern)
                ->orWhere('phone', 'like', $searchPattern);
        });
    }
}
