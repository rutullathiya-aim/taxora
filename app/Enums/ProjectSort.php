<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasOptions;
use Illuminate\Database\Eloquent\Builder;

enum ProjectSort: string
{
    use HasOptions;

    case Latest = 'latest';
    case Oldest = 'oldest';
    case NameAsc = 'name_asc';
    case NameDesc = 'name_desc';
    case DueDateAsc = 'due_date_asc';
    case DueDateDesc = 'due_date_desc';

    public function label(): string
    {
        return match ($this) {
            self::Latest => 'Latest',
            self::Oldest => 'Oldest',
            self::NameAsc => 'Name (ASC)',
            self::NameDesc => 'Name (DESC)',
            self::DueDateAsc => 'Due Date (ASC)',
            self::DueDateDesc => 'Due Date (DESC)',
        };
    }

    public function apply(Builder $query): void
    {
        match ($this) {
            self::Latest => $query->orderByDesc('created_at')->orderByDesc('id'),
            self::Oldest => $query->orderBy('created_at')->orderBy('id'),
            self::NameAsc => $query->orderBy('project_name', 'asc')->orderBy('id'),
            self::NameDesc => $query->orderBy('project_name', 'desc')->orderBy('id'),
            self::DueDateAsc => $query->orderByRaw('due_date IS NULL')->orderBy('due_date', 'asc')->orderBy('id'),
            self::DueDateDesc => $query->orderByRaw('due_date IS NULL')->orderBy('due_date', 'desc')->orderBy('id'),
        };
    }
}
