<?php

namespace App\Enums;

enum ChecklistStatus: string
{
    case Pending = 'Pending';
    case Submitted = 'Submitted';
    case Approved = 'Approved';
    case Rejected = 'Rejected';
    case NotApplicable = 'Not Applicable';

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Submitted => 'blue',
            self::Approved => 'emerald',
            self::Rejected => 'rose',
            self::NotApplicable => 'zinc',
        };
    }

    /**
     * @return array<int, self>
     */
    public static function completionStatuses(): array
    {
        return [
            self::Approved,
            self::Submitted,
            self::NotApplicable,
        ];
    }

    public function selectClasses(): string
    {
        return match ($this) {
            self::Pending => '!text-amber-600 !border-amber-200 !bg-amber-50/50 dark:!border-amber-500/20 dark:!text-amber-400 dark:!bg-amber-500/10',
            self::Submitted => '!text-blue-600 !border-blue-200 !bg-blue-50/50 dark:!border-blue-500/20 dark:!text-blue-400 dark:!bg-blue-500/10',
            self::Approved => '!text-emerald-600 !border-emerald-200 !bg-emerald-50/50 dark:!border-emerald-500/20 dark:!text-emerald-400 dark:!bg-emerald-500/10',
            self::Rejected => '!text-rose-600 !border-rose-200 !bg-rose-50/50 dark:!border-rose-500/20 dark:!text-rose-400 dark:!bg-rose-500/10',
            self::NotApplicable => '!text-zinc-600 !border-zinc-200 !bg-zinc-50/50 dark:!border-zinc-500/20 dark:!text-zinc-400 dark:!bg-zinc-500/10',
        };
    }
}
