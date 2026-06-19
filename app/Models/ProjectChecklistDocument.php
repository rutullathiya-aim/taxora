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
        'document_id',
    ];

    public function Document(): BelongsTo
    {
        return $this->belongsTo(Document::class)->withTrashed();
    }

    public function projectChecklist(): BelongsTo
    {
        return $this->belongsTo(ProjectChecklist::class);
    }
}
