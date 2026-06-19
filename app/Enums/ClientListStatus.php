<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum ClientListStatus: string
{
    use HasOptions;

    case All = 'all';
    case Active = 'active';
    case Inactive = 'inactive';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::All => 'All',
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Deleted => 'Deleted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::All => 'zinc',
            self::Active => 'green',
            self::Inactive => 'zinc',
            self::Deleted => 'red',
        };
    }

    public static function fromState(ClientStatus $status, bool $trashed): self
    {
        if ($trashed) {
            return self::Deleted;
        }

        return match ($status) {
            ClientStatus::Active => self::Active,
            ClientStatus::Inactive => self::Inactive,
        };
    }
}
