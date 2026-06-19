<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum ProjectListStatus: string
{
    use HasOptions;

    case All = 'all';
    case Active = 'active';
    case Completed = 'completed';
    case OnHold = 'on_hold';
    case Cancelled = 'cancelled';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::All => 'All',
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::OnHold => 'On Hold',
            self::Cancelled => 'Cancelled',
            self::Deleted => 'Deleted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::All => 'zinc',
            self::Active => 'blue',
            self::Completed => 'green',
            self::OnHold => 'amber',
            self::Cancelled => 'red',
            self::Deleted => 'rose',
        };
    }

    public static function fromState(ProjectStatus $status, bool $trashed): self
    {
        if ($trashed) {
            return self::Deleted;
        }

        return match ($status) {
            ProjectStatus::Active => self::Active,
            ProjectStatus::Completed => self::Completed,
            ProjectStatus::OnHold => self::OnHold,
            ProjectStatus::Cancelled => self::Cancelled,
        };
    }
}
