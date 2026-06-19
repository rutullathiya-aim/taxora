<?php

use App\Enums\UserRole;
use App\Livewire\Team\TeamForm;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->manager = User::factory()->manager()->create();
});

it('allows admin to create a new team member', function () {
    $this->actingAs($this->admin);

    Livewire::test(TeamForm::class)
        ->call('openCreateModal')
        ->set('name', 'John Doe')
        ->set('email', 'john.doe@example.com')
        ->set('phone', '9876543210')
        ->set('role', 'staff')
        ->call('save')
        ->assertHasNoErrors();

    $newUser = User::where('email', 'john.doe@example.com')->first();
    expect($newUser)->not->toBeNull();
    expect($newUser->name)->toBe('John Doe');
    expect($newUser->phone)->toBe('9876543210');
    expect($newUser->role)->toBe(UserRole::Staff);
    expect($newUser->hasRole('staff'))->toBeTrue();
});

it('allows manager to create a team member only with staff role', function () {
    $this->actingAs($this->manager);

    // 1. Trying to assign admin role should fail
    Livewire::test(TeamForm::class)
        ->call('openCreateModal')
        ->set('name', 'Manager Created')
        ->set('email', 'mgr.created.admin@example.com')
        ->set('phone', '9876543211')
        ->set('role', 'admin')
        ->call('save')
        ->assertHasErrors(['role']);

    // 2. Trying to assign manager role should fail
    Livewire::test(TeamForm::class)
        ->call('openCreateModal')
        ->set('name', 'Manager Created')
        ->set('email', 'mgr.created.mgr@example.com')
        ->set('phone', '9876543212')
        ->set('role', 'manager')
        ->call('save')
        ->assertHasErrors(['role']);

    // 3. Trying to assign staff role should succeed
    Livewire::test(TeamForm::class)
        ->call('openCreateModal')
        ->set('name', 'Manager Created Staff')
        ->set('email', 'mgr.created.staff@example.com')
        ->set('phone', '9876543213')
        ->set('role', 'staff')
        ->call('save')
        ->assertHasNoErrors();

    $newStaff = User::where('email', 'mgr.created.staff@example.com')->first();
    expect($newStaff)->not->toBeNull();
    expect($newStaff->role)->toBe(UserRole::Staff);
    expect($newStaff->hasRole('staff'))->toBeTrue();
});
