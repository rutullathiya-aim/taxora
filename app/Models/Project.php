<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'client_id',
        'project_name',
        'service_id',
        'status',
        'due_date',
    ];

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
     * @return HasMany<ProjectChecklist, $this>
     */
    public function projectChecklists(): HasMany
    {
        return $this->hasMany(ProjectChecklist::class);
    }

    public function getProgressAttribute(): int
    {
        $total = $this->projectChecklists->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->projectChecklists->whereIn('status', ['Approved', 'Not Applicable'])->count();

        return (int) round(($completed / $total) * 100);
    }
}
