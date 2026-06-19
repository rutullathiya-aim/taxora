<?php

declare(strict_types=1);

namespace App\Enums;

enum ChecklistAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Reordered = 'reordered';

    public function message(): string
    {
        return match ($this) {
            self::Created => 'Checklist item added.',
            self::Updated => 'Checklist item updated.',
            self::Deleted => 'Checklist item removed.',
            self::Reordered => 'Checklist items reordered.',
        };
    }
}
