<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TeamPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create team permissions
        $permissions = [
            'view team',
            'create team members',
            'edit team members',
            'delete team members',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign permissions

        $admin = Role::findOrCreate('admin');
        $admin->givePermissionTo(Permission::all());

        $manager = Role::findOrCreate('manager');
        $manager->givePermissionTo(['view team']);

        $staff = Role::findOrCreate('staff');
        // Staff has no permissions by default

        // Let's also sync existing users with their current roles
        $users = User::all();
        foreach ($users as $user) {
            $user->assignRole($user->role);
        }
    }
}
