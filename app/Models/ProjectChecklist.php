<?php

namespace App\Models;

use App\Enums\ChecklistStatus;
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
        'description',
        'is_mandatory',
        'is_manual',
        'status',
        'remarks',
        'requested_at',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'is_manual' => 'boolean',
            'status' => ChecklistStatus::class,
        ];
    }

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
