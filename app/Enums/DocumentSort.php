<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum DocumentSort: string
{
    use HasOptions;

    case Latest = 'latest';
    case Oldest = 'oldest';
    case NameAsc = 'name_asc';
    case NameDesc = 'name_desc';
    case SizeLargest = 'size_largest';
    case SizeSmallest = 'size_smallest';

    public function label(): string
    {
        return match ($this) {
            self::Latest => 'Latest',
            self::Oldest => 'Oldest',
            self::NameAsc => 'Name (ASC)',
            self::NameDesc => 'Name (DESC)',
            self::SizeLargest => 'Largest Size',
            self::SizeSmallest => 'Smallest Size',
        };
    }
}
