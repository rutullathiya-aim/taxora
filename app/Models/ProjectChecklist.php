<?php

namespace App\Models;

use Database\Factories\ProjectChecklistFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectChecklist extends Model
{
    /** @use HasFactory<ProjectChecklistFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'project_id',
        'name',
        'is_mandatory',
        'status',
        'remarks',
        'requested_at',
        'approved_at',
        'approved_by',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function documents()
    {
        return $this->hasMany(ProjectChecklistDocument::class);
    }
}
