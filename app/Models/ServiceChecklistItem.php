<?php

namespace App\Models;

use App\Enums\ServiceChecklistItemStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceChecklistItem extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'service_checklist_items';

    protected $fillable = [
        'service_id',
        'title',
        'description',
        'is_mandatory',
        'sort_order',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'status' => ServiceChecklistItemStatus::class,
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
