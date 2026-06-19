<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ServiceListStatus;
use App\Enums\ServiceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property ServiceStatus $status
 * @property string|null $created_by
 * @property-read User|null $createdBy
 * @property-read Collection<int, Project> $projects
 * @property-read Collection<int, ServiceChecklistItem> $checklistItems
 */
class Service extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ServiceStatus::class,
        ];
    }

    public function listStatus(): ServiceListStatus
    {
        return ServiceListStatus::fromState($this->status, $this->trashed());
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<ServiceChecklistItem, $this>
     */
    public function checklistItems(): HasMany
    {
        return $this->hasMany(ServiceChecklistItem::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query;
    }

    public function scopeStatus(Builder $query, ServiceStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeFilterStatus(Builder $query, ServiceListStatus $status): Builder
    {
        return match ($status) {
            ServiceListStatus::Active => $query->status(ServiceStatus::Active),
            ServiceListStatus::Inactive => $query->status(ServiceStatus::Inactive),
            ServiceListStatus::Deleted => $query->onlyTrashed(),
            ServiceListStatus::All => $query,
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
            $sub->where('name', 'like', $searchPattern)
                ->orWhere('description', 'like', $searchPattern);
        });
    }
}
