<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            return true;
        }

        return $task->assignees()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    public function addComment(User $user, Task $task): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            return true;
        }

        return $task->assignees()->where('users.id', $user->id)->exists();
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    public function updateStatus(User $user, Task $task): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            return true;
        }

        return $task->assignees()->where('users.id', $user->id)->exists();
    }
}
