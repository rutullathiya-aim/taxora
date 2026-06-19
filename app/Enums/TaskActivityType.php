<?php

namespace App\Enums;

enum TaskActivityType: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Assigned = 'assigned';
    case Reassigned = 'reassigned';
    case StatusChanged = 'status_changed';
    case Completed = 'completed';
}
