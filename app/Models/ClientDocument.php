<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientDocument extends Model
{
    use HasUlids;

    protected $fillable = [
        'client_id',
        'name',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function projectChecklistDocuments(): HasMany
    {
        return $this->hasMany(ProjectChecklistDocument::class);
    }
}
