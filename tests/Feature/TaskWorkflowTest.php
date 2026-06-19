<?php

use App\Livewire\Tasks\Index;
use App\Livewire\Tasks\TaskForm;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('calculates task statistics correctly where total includes open tasks (todo, in progress, and on hold)', function () {
    $this->actingAs($this->admin);

    // Create 2 todo tasks
    Task::factory()->todo()->count(2)->create(['created_by' => $this->admin->id]);

    // Create 3 in progress tasks
    Task::factory()->inProgress()->count(3)->create(['created_by' => $this->admin->id]);

    // Create 4 completed tasks
    Task::factory()->completed()->count(4)->create(['created_by' => $this->admin->id]);

    // Create 2 on_hold tasks
    Task::factory()->onHold()->count(2)->create(['created_by' => $this->admin->id]);

    // Create 1 overdue todo task (overdue is due in past and not completed/cancelled)
    Task::factory()->overdue()->create(['created_by' => $this->admin->id]);

    $component = Livewire::test(Index::class);

    $stats = $component->get('stats');

    // todo tasks = 2 + 1 (overdue is todo status by default) = 3
    expect($stats['todo'])->toBe(3);
    // in progress tasks = 3
    expect($stats['in_progress'])->toBe(3);
    // completed tasks = 4
    expect($stats['completed'])->toBe(4);
    // on hold tasks = 2
    expect($stats['on_hold'])->toBe(2);
    // overdue tasks = 1
    expect($stats['overdue'])->toBe(1);
    // total tasks should include todo (3), in progress (3), and on hold (2) = 8
    expect($stats['total'])->toBe(8);
});

it('allows deleting a task', function () {
    $this->actingAs($this->admin);

    $task = Task::factory()->todo()->create(['created_by' => $this->admin->id]);

    expect(Task::count())->toBe(1);

    Livewire::test(Index::class)
        ->dispatch('delete-task', $task->id)
        ->assertHasNoErrors();

    // The task should be soft deleted (since SoftDeletes trait is used)
    expect(Task::count())->toBe(0);
    expect(Task::withTrashed()->count())->toBe(1);
});

it('defaults to showing only open tasks (todo, in progress, on hold)', function () {
    $this->actingAs($this->admin);

    Task::factory()->todo()->create(['title' => 'Todo Task', 'created_by' => $this->admin->id]);
    Task::factory()->inProgress()->create(['title' => 'In Progress Task', 'created_by' => $this->admin->id]);
    Task::factory()->onHold()->create(['title' => 'On Hold Task', 'created_by' => $this->admin->id]);
    Task::factory()->completed()->create(['title' => 'Completed Task', 'created_by' => $this->admin->id]);

    $component = Livewire::test(Index::class);
    $tasks = $component->get('tasks');
    $titles = $tasks->pluck('title')->toArray();

    expect($titles)->toContain('Todo Task')
        ->toContain('In Progress Task')
        ->toContain('On Hold Task')
        ->not->toContain('Completed Task');
});

it('allows creating a task without a due date', function () {
    $this->actingAs($this->admin);

    Livewire::test(TaskForm::class)
        ->dispatch('create-task')
        ->set('title', 'Task without due date')
        ->set('status', 'todo')
        ->set('priority', 'medium')
        ->set('due_at', null)
        ->call('save')
        ->assertHasNoErrors();

    $task = Task::where('title', 'Task without due date')->first();
    expect($task)->not->toBeNull();
    expect($task->due_at)->toBeNull();
});

it('allows assigning a task to multiple users and tracks activities', function () {
    $this->actingAs($this->admin);

    Notification::fake();

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    Livewire::test(TaskForm::class)
        ->dispatch('create-task')
        ->set('title', 'Multi Assign Task')
        ->set('status', 'todo')
        ->set('priority', 'medium')
        ->set('assigned_to', [$user1->id, $user2->id])
        ->call('save')
        ->assertHasNoErrors();

    $task = Task::where('title', 'Multi Assign Task')->first();
    expect($task->assignees->pluck('id')->toArray())->toEqualCanonicalizing([$user1->id, $user2->id]);

    // Check activities
    $activities = $task->activities()->pluck('type')->toArray();
    expect($activities)->toContain('created');
    expect($activities)->toContain('assigned');

    // Test reassignment (remove user2, add user3)
    Livewire::test(TaskForm::class)
        ->dispatch('edit-task', id: $task->id)
        ->set('assigned_to', [$user1->id, $user3->id])
        ->call('save')
        ->assertHasNoErrors();

    $task->refresh();
    expect($task->assignees->pluck('id')->toArray())->toEqualCanonicalizing([$user1->id, $user3->id]);
});
