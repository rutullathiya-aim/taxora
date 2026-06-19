<?php

namespace App\Enums;

enum DocumentAction: string
{
    case Delete = 'delete';
    case Restore = 'restore';
    case ForceDelete = 'force_delete';
    case Rename = 'rename';
}
