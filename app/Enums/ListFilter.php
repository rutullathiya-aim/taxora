<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum ListFilter: string
{
    use HasOptions;

    case All = 'all';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::All => 'All',
            self::Deleted => 'Deleted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::All => 'zinc',
            self::Deleted => 'red',
        };
    }
}
