<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ChecklistStatus;
use App\Enums\ServiceChecklistItemStatus;
use App\Models\Project;
use App\Models\ProjectChecklist;
use App\Models\ServiceChecklistItem;
use Illuminate\Support\Str;

class ProjectChecklistSeeder
{
    public function seed(Project $project): void
    {
        $now = now();

        $items = ServiceChecklistItem::query()
            ->where('service_id', $project->service_id)
            ->where('status', ServiceChecklistItemStatus::Active->value)
            ->orderBy('sort_order')
            ->get()
            ->map(function (ServiceChecklistItem $item) use ($project, $now) {
                return [
                    'id' => (string) Str::ulid(),
                    'project_id' => $project->id,
                    'name' => $item->title,
                    'description' => $item->description,
                    'is_mandatory' => $item->is_mandatory,
                    'status' => ChecklistStatus::Pending->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->toArray();

        if ($items !== []) {
            ProjectChecklist::insert($items);
        }
    }
}
