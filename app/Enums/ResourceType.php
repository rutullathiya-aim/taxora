<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Support\Str;

enum ResourceType: string
{
    case Client = 'client';
    case Service = 'service';
    case Project = 'project';
    case Task = 'task';
    case Team = 'team';
    case Document = 'document';

    public function label(): string
    {
        return Str::of($this->value)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }
}
