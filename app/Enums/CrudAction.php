<?php

declare(strict_types=1);

namespace App\Enums;

enum CrudAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Restored = 'restored';
    case ForceDeleted = 'force_deleted';

    public function messageTemplate(): string
    {
        return match ($this) {
            self::Created => '%s created successfully.',
            self::Updated => '%s updated successfully.',
            self::Deleted => '%s moved to trash.',
            self::Restored => '%s restored successfully.',
            self::ForceDeleted => '%s permanently deleted.',
        };
    }

    public function message(ResourceType $resource): string
    {
        return sprintf(
            $this->messageTemplate(),
            $resource->label(),
        );
    }
}
