<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum UserListStatus: string
{
    use HasOptions;

    case All = 'all';
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::All => 'All',
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Pending => 'Pending',
            self::Deleted => 'Deleted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::All => 'zinc',
            self::Active => 'green',
            self::Inactive => 'zinc',
            self::Pending => 'amber',
            self::Deleted => 'red',
        };
    }

    public static function fromState(UserStatus $status, bool $trashed): self
    {
        if ($trashed) {
            return self::Deleted;
        }

        return match ($status) {
            UserStatus::Active => self::Active,
            UserStatus::Inactive => self::Inactive,
            UserStatus::Pending => self::Pending,
        };
    }
}
