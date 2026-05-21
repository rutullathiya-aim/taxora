<?php

namespace App\Enums;

enum UserStatus: string
{
    case PendingInvitation = 'pending_invitation';
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::PendingInvitation => 'Pending Invitation',
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingInvitation => 'zinc',
            self::Active => 'green',
            self::Inactive => 'red',
        };
    }
}
