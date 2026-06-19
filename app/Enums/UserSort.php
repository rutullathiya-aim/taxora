<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasOptions;
use Illuminate\Database\Eloquent\Builder;

enum UserSort: string
{
    use HasOptions;

    case Latest = 'latest';
    case Oldest = 'oldest';
    case NameAsc = 'name_asc';
    case NameDesc = 'name_desc';

    public function label(): string
    {
        return match ($this) {
            self::Latest => 'Latest',
            self::Oldest => 'Oldest',
            self::NameAsc => 'Name (ASC)',
            self::NameDesc => 'Name (DESC)',
        };
    }

    public function apply(Builder $query): void
    {
        match ($this) {
            self::Latest => $query->latest()->orderByDesc('id'),
            self::Oldest => $query->oldest()->orderBy('id'),
            self::NameAsc => $query->orderBy('name', 'asc')->orderBy('id'),
            self::NameDesc => $query->orderBy('name', 'desc')->orderBy('id'),
        };
    }
}
