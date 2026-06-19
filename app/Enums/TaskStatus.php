<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum TaskStatus: string
{
    use HasOptions;

    case Todo = 'todo';
    case InProgress = 'in_progress';
    case OnHold = 'on_hold';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'To Do',
            self::InProgress => 'In Progress',
            self::OnHold => 'On Hold',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Todo => 'amber',
            self::InProgress => 'blue',
            self::OnHold => 'orange',
            self::Completed => 'green',
            self::Cancelled => 'zinc',
        };
    }

    public function selectClasses(): string
    {
        return match ($this) {
            self::Todo => '!text-amber-600 !border-amber-200 !bg-amber-50/50 dark:!border-amber-500/20 dark:!text-amber-400 dark:!bg-amber-500/10',
            self::InProgress => '!text-blue-600 !border-blue-200 !bg-blue-50/50 dark:!border-blue-500/20 dark:!text-blue-400 dark:!bg-blue-500/10',
            self::OnHold => '!text-orange-600 !border-orange-200 !bg-orange-50/50 dark:!border-orange-500/20 dark:!text-orange-400 dark:!bg-orange-500/10',
            self::Completed => '!text-emerald-600 !border-emerald-200 !bg-emerald-50/50 dark:!border-emerald-500/20 dark:!text-emerald-400 dark:!bg-emerald-500/10',
            self::Cancelled => '!text-zinc-600 !border-zinc-200 !bg-zinc-50/50 dark:!border-zinc-500/20 dark:!text-zinc-400 dark:!bg-zinc-500/10',
        };
    }
}
