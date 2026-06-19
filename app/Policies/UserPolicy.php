<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->role === UserRole::Manager) {
            return true;
        }

        return $user->hasPermissionTo('create team members');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->role === UserRole::Manager && $model->role === UserRole::Staff) {
            return true;
        }

        return $user->hasPermissionTo('edit team members');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->role === UserRole::Manager && $model->role === UserRole::Staff) {
            return true;
        }

        return $user->hasPermissionTo('delete team members');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        if ($user->role === UserRole::Manager && $model->role === UserRole::Staff) {
            return true;
        }

        return $user->hasPermissionTo('delete team members'); // Treating restore like delete
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        if ($user->role === UserRole::Manager && $model->role === UserRole::Staff) {
            return true;
        }

        return $user->hasPermissionTo('delete team members');
    }
}
