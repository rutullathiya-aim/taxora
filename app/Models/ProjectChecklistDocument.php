<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectChecklistDocument extends Model
{
    use HasUlids;
    protected $fillable = [
        'project_checklist_id',
        'client_document_id',
    ];

    public function clientDocument(): BelongsTo
    {
        return $this->belongsTo(ClientDocument::class);
    }

    public function projectChecklist(): BelongsTo
    {
        return $this->belongsTo(ProjectChecklist::class);
    }
}
