<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum TaskSort: string
{
    use HasOptions;

    case Latest = 'latest';
    case Oldest = 'oldest';
    case TitleAsc = 'title_asc';
    case TitleDesc = 'title_desc';
    case DueAsc = 'due_asc';
    case DueDesc = 'due_desc';

    public function label(): string
    {
        return match ($this) {
            self::Latest => 'Latest',
            self::Oldest => 'Oldest',
            self::TitleAsc => 'Title (ASC)',
            self::TitleDesc => 'Title (DESC)',
            self::DueAsc => 'Due Date (Asc)',
            self::DueDesc => 'Due Date (Desc)',
        };
    }
}
