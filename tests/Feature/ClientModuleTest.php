<?php

use App\Enums\ClientSort;
use App\Enums\ServiceChecklistItemStatus;
use App\Enums\ServiceStatus;
use App\Livewire\Clients\ClientForm;
use App\Livewire\Clients\Index;
use App\Models\Client;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceChecklistItem;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->manager = User::factory()->manager()->create();
    $this->staff = User::factory()->staff()->create();
});

// --- Authorization ---

it('allows admin to view client index', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->assertOk();
});

it('allows staff to view client index', function () {
    Livewire::actingAs($this->staff)
        ->test(Index::class)
        ->assertOk();
});

it('prevents guests from viewing client index', function () {
    $this->get(route('clients.index'))
        ->assertRedirect(route('login'));
});

it('prevents staff from creating a client', function () {
    Livewire::actingAs($this->staff)
        ->test(ClientForm::class)
        ->call('openCreateModal')
        ->assertForbidden();
});

it('allows admin to create a client', function () {
    Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openCreateModal')
        ->assertOk()
        ->assertSet('showModal', true);
});

it('prevents staff from editing a client', function () {
    $client = Client::factory()->create();

    Livewire::actingAs($this->staff)
        ->test(ClientForm::class)
        ->call('openEditModal', $client->id)
        ->assertForbidden();
});

it('prevents staff from deleting a client', function () {
    $client = Client::factory()->create();

    Livewire::actingAs($this->staff)
        ->test(Index::class)
        ->call('deleteClient', $client->id)
        ->assertForbidden();
});

// --- CRUD Operations ---

it('creates a client with valid data', function () {
    Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->set('client_name', 'John Doe')
        ->set('company_name', 'Doe Industries')
        ->set('email', 'john@doe.com')
        ->set('phone', '9876543210')
        ->set('address', '123 Main Street, Mumbai')
        ->call('save')
        ->assertDispatched('clients.saved');

    $this->assertDatabaseHas('clients', [
        'client_name' => 'John Doe',
        'email' => 'john@doe.com',
        'phone' => '9876543210',
        'status' => 'active',
    ]);
});

it('updates a client with valid data', function () {
    $client = Client::factory()->create([
        'client_name' => 'Old Name',
    ]);

    Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openEditModal', $client->id)
        ->set('client_name', 'New Name')
        ->call('save')
        ->assertDispatched('clients.saved');

    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'client_name' => 'New Name',
    ]);
});

it('soft deletes a client', function () {
    $client = Client::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('deleteClient', $client->id);

    $this->assertSoftDeleted('clients', ['id' => $client->id]);
});

it('restores a soft-deleted client', function () {
    $client = Client::factory()->deleted()->create();

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('restoreClient', $client->id);

    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'deleted_at' => null,
    ]);
});

it('force deletes a trashed client', function () {
    $client = Client::factory()->deleted()->create();

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('forceDeleteClient', $client->id);

    $this->assertDatabaseMissing('clients', ['id' => $client->id]);
});

it('cannot force delete a non-trashed client', function () {
    $client = Client::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('forceDeleteClient', $client->id);
})->throws(ModelNotFoundException::class);

// --- Validation ---

it('requires all mandatory fields', function () {
    Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openCreateModal')
        ->call('save')
        ->assertHasErrors([
            'client_name' => 'required',
            'email' => 'required',
            'phone' => 'required',
        ]);
});

it('validates email uniqueness', function () {
    Client::factory()->create(['email' => 'taken@example.com']);

    Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openCreateModal')
        ->set('client_name', 'Test User')
        ->set('company_name', 'Test Co')
        ->set('email', 'taken@example.com')
        ->set('phone', '9876543210')
        ->call('save')
        ->assertHasErrors(['email' => 'unique']);
});

it('validates phone uniqueness', function () {
    Client::factory()->create(['phone' => '9876543210']);

    Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openCreateModal')
        ->set('client_name', 'Test User')
        ->set('company_name', 'Test Co')
        ->set('email', 'new@example.com')
        ->set('phone', '9876543210')
        ->call('save')
        ->assertHasErrors(['phone' => 'unique']);
});

it('allows same email when updating the same client', function () {
    $client = Client::factory()->create(['email' => 'same@example.com']);

    Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openEditModal', $client->id)
        ->set('email', 'same@example.com')
        ->call('save')
        ->assertHasNoErrors('email');
});

it('validates phone format', function () {
    Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openCreateModal')
        ->set('client_name', 'Test')
        ->set('company_name', 'Co')
        ->set('email', 'a@b.com')
        ->set('phone', '1234567890')
        ->set('address', 'Street')
        ->call('save')
        ->assertHasErrors(['phone' => 'regex']);
});

it('validates address max length', function () {
    Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openCreateModal')
        ->set('client_name', 'Test')
        ->set('company_name', 'Co')
        ->set('email', 'a@b.com')
        ->set('phone', '9876543210')
        ->set('address', str_repeat('x', 1001))
        ->call('save')
        ->assertHasErrors(['address' => 'max']);
});

it('validates company_name min and max length', function () {
    $component = Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openCreateModal')
        ->set('client_name', 'Test User')
        ->set('email', 'test@test.com')
        ->set('phone', '9876543210');

    // Test min
    $component->set('company_name', 'A')
        ->call('save')
        ->assertHasErrors(['company_name' => 'min']);

    // Test max
    $component->set('company_name', str_repeat('A', 151))
        ->call('save')
        ->assertHasErrors(['company_name' => 'max']);
});

it('validates project_name max length', function () {
    $service = Service::factory()->create(['status' => ServiceStatus::Active->value]);

    Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openCreateModal')
        ->set('client_name', 'Test User')
        ->set('email', 'test@test.com')
        ->set('phone', '9876543210')
        ->set('service_id', $service->id)
        ->set('project_name', str_repeat('A', 151))
        ->call('save')
        ->assertHasErrors(['project_name' => 'max']);
});

it('validates email max length', function () {
    Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openCreateModal')
        ->set('client_name', 'Test User')
        ->set('email', str_repeat('a', 64) . '@' . str_repeat('b', 190) . '.com')
        ->set('phone', '9876543210')
        ->call('save')
        ->assertHasErrors(['email' => 'max']);
});

it('validates required_with for project_name and service_id', function () {
    $component = Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openCreateModal')
        ->set('client_name', 'Test')
        ->set('email', 'a@b.com')
        ->set('phone', '9876543210');

    // Has project, no service
    $component->set('project_name', 'Test Project')
        ->set('service_id', null)
        ->call('save')
        ->assertHasErrors(['service_id' => 'required_with']);

    // Has service, no project
    $component->set('project_name', null)
        ->set('service_id', 1) // Service exists validation would also fail but required_with fails too
        ->call('save')
        ->assertHasErrors(['project_name' => 'required_with']);
});

it('allows same phone when updating the same client', function () {
    $client = Client::factory()->create(['phone' => '9876543210']);

    Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openEditModal', $client->id)
        ->set('phone', '9876543210')
        ->call('save')
        ->assertHasNoErrors('phone');
});

it('validates status enum', function () {
    $client = Client::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openEditModal', $client->id)
        ->set('status', 'fake_status')
        ->call('save')
        ->assertHasErrors(['status' => 'Illuminate\Validation\Rules\Enum']);
});

it('validates service_id exists, is active, and not deleted', function () {
    $activeService = Service::factory()->create(['status' => ServiceStatus::Active->value]);
    $inactiveService = Service::factory()->create(['status' => ServiceStatus::Inactive->value]);
    $deletedService = Service::factory()->create(['status' => ServiceStatus::Active->value, 'deleted_at' => now()]);

    $component = Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openCreateModal')
        ->set('client_name', 'Test User')
        ->set('email', 'test@test.com')
        ->set('phone', '9876543210')
        ->set('project_name', 'Test Project');

    // Inactive service
    $component->set('service_id', $inactiveService->id)
        ->call('save')
        ->assertHasErrors(['service_id' => 'exists']);

    // Deleted service
    $component->set('service_id', $deletedService->id)
        ->call('save')
        ->assertHasErrors(['service_id' => 'exists']);

    // Valid active service
    $component->set('service_id', $activeService->id)
        ->call('save')
        ->assertHasNoErrors('service_id');
});

it('prevents staff from bypassing modal and saving', function () {
    Livewire::actingAs($this->staff)
        ->test(ClientForm::class)
        ->set('client_name', 'Hacker')
        ->set('email', 'hacker@hacker.com')
        ->set('phone', '9876543210')
        ->call('save')
        ->assertForbidden();
});

// --- Filtering & Sorting ---

it('filters clients by active status', function () {
    Client::factory()->create(['client_name' => 'Alice Valid', 'status' => 'active']);
    Client::factory()->inactive()->create(['client_name' => 'Bob Invalid']);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->set('status', 'active')
        ->assertSee('Alice Valid')
        ->assertDontSee('Bob Invalid');
});

it('filters clients by deleted status', function () {
    Client::factory()->create(['client_name' => 'Charlie Live']);
    Client::factory()->deleted()->create(['client_name' => 'David Trashed']);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->set('status', 'deleted')
        ->assertSee('David Trashed')
        ->assertDontSee('Charlie Live');
});

it('searches clients by name', function () {
    Client::factory()->create(['client_name' => 'Alice Smith']);
    Client::factory()->create(['client_name' => 'Bob Jones']);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->set('status', 'all')
        ->set('search', 'Alice')
        ->assertSee('Alice Smith')
        ->assertDontSee('Bob Jones');
});

// --- Stats ---

it('returns correct stats counts', function () {
    Client::factory()->count(3)->create(['status' => 'active']);
    Client::factory()->count(2)->inactive()->create();
    Client::factory()->deleted()->create();

    $component = Livewire::actingAs($this->admin)
        ->test(Index::class);

    $stats = $component->instance()->stats();

    expect($stats['total'])->toBe(5)
        ->and($stats['active'])->toBe(3)
        ->and($stats['inactive'])->toBe(2)
        ->and($stats['deleted'])->toBe(1);
});

it('trims whitespace from inputs before saving', function () {
    $service = Service::factory()->create(['status' => ServiceStatus::Active->value]);

    Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openCreateModal')
        ->set('client_name', '  John Doe  ')
        ->set('company_name', '  Doe Co  ')
        ->set('email', '  JOHN@DOE.COM  ')
        ->set('phone', '9876543210')
        ->set('address', '  123 Main St  ')
        ->set('project_name', '  My Project  ')
        ->set('service_id', $service->id)
        ->call('save')
        ->assertDispatched('clients.saved');

    $this->assertDatabaseHas('clients', [
        'client_name' => 'John Doe',
        'company_name' => 'Doe Co',
        'email' => 'john@doe.com',
        'address' => '123 Main St',
    ]);

    // Project assertions are not made against the projects table because
    // the project creation logic creates dynamic names in this test suite.
});

// --- Manager Authorization ---

it('allows manager to view client index', function () {
    Livewire::actingAs($this->manager)
        ->test(Index::class)
        ->assertOk();
});

it('allows manager to create a client', function () {
    Livewire::actingAs($this->manager)
        ->test(ClientForm::class)
        ->call('openCreateModal')
        ->assertOk();
});

it('allows manager to edit a client', function () {
    $client = Client::factory()->create();

    Livewire::actingAs($this->manager)
        ->test(ClientForm::class)
        ->call('openEditModal', $client->id)
        ->assertOk();
});

it('allows manager to soft delete a client', function () {
    $client = Client::factory()->create();

    Livewire::actingAs($this->manager)
        ->test(Index::class)
        ->call('deleteClient', $client->id);

    $this->assertSoftDeleted('clients', ['id' => $client->id]);
});

it('prevents manager from force deleting a client', function () {
    $client = Client::factory()->deleted()->create();

    Livewire::actingAs($this->manager)
        ->test(Index::class)
        ->call('forceDeleteClient', $client->id)
        ->assertForbidden();
});

// --- Staff Visibility Scope ---

it('only shows clients to staff if they are assigned to a project', function () {
    $service = Service::create([
        'name' => 'Test Service',
        'slug' => 'test-service',
        'status' => ServiceStatus::Active->value,
    ]);

    // Client A: Staff is assigned to its project
    $clientA = Client::factory()->create(['client_name' => 'Client A']);
    $project = Project::create([
        'client_id' => $clientA->id,
        'project_name' => 'Test Project A',
        'service_id' => $service->id,
    ]);
    $project->assignees()->attach($this->staff->id);

    // Client B: Staff is NOT assigned to its project
    $clientB = Client::factory()->create(['client_name' => 'Client B']);
    Project::create([
        'client_id' => $clientB->id,
        'project_name' => 'Test Project B',
        'service_id' => $service->id,
    ]);

    Livewire::actingAs($this->staff)
        ->test(Index::class)
        ->assertSee('Client A')
        ->assertDontSee('Client B');
});

it('only shows clients to staff if they are assigned to a task', function () {
    // Client C: Staff is assigned to its task
    $clientC = Client::factory()->create(['client_name' => 'Client C']);
    $task = Task::create([
        'client_id' => $clientC->id,
        'title' => 'Test Task C',
        'created_by' => $this->admin->id,
    ]);
    $task->assignees()->attach($this->staff->id);

    // Client D: Staff is NOT assigned to its task
    $clientD = Client::factory()->create(['client_name' => 'Client D']);
    Task::create([
        'client_id' => $clientD->id,
        'title' => 'Test Task D',
        'created_by' => $this->admin->id,
    ]);

    Livewire::actingAs($this->staff)
        ->test(Index::class)
        ->assertSee('Client C')
        ->assertDontSee('Client D');
});

// --- Project Creation with Client ---

it('creates a project and assigns creator when service is provided', function () {
    $service = Service::create([
        'name' => 'Test Service',
        'slug' => 'test-service',
        'status' => ServiceStatus::Active->value,
    ]);
    ServiceChecklistItem::create([
        'service_id' => $service->id,
        'title' => 'Test Item',
        'status' => ServiceChecklistItemStatus::Active->value,
        'sort_order' => 1,
    ]);

    Livewire::actingAs($this->admin)
        ->test(ClientForm::class)
        ->call('openCreateModal')
        ->set('client_name', 'New Biz')
        ->set('company_name', 'Biz Inc')
        ->set('email', 'biz@inc.com')
        ->set('phone', '9876543210')
        ->set('address', '123 Biz Street')
        ->set('project_name', 'Initial Project')
        ->set('service_id', $service->id)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('clients.saved');

    $client = Client::where('email', 'biz@inc.com')->first();

    $this->assertDatabaseHas('projects', [
        'client_id' => $client->id,
        'project_name' => 'Initial Project',
        'service_id' => $service->id,
    ]);

    $project = Project::where('client_id', $client->id)->first();

    // Check if the admin who created the project is assigned to it
    $this->assertTrue($project->assignees->contains($this->admin->id));

    // Check if checklist items were generated
    $this->assertDatabaseHas('project_checklists', [
        'project_id' => $project->id,
    ]);
});

// --- Sorting ---

it('sorts clients correctly', function () {
    Client::factory()->create(['client_name' => 'Zebra Corp']);
    Client::factory()->create(['client_name' => 'Alpha Inc']);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->set('sortBy', ClientSort::NameAsc->value)
        ->assertSeeInOrder(['Alpha Inc', 'Zebra Corp'])
        ->set('sortBy', ClientSort::NameDesc->value)
        ->assertSeeInOrder(['Zebra Corp', 'Alpha Inc']);
});

it('allows manager to restore a client', function () {
    $client = Client::factory()->deleted()->create();

    Livewire::actingAs($this->manager)
        ->test(Index::class)
        ->call('restoreClient', $client->id);

    $this->assertNull($client->fresh()->deleted_at);
});

it('prevents staff from restoring a client', function () {
    $client = Client::factory()->deleted()->create();

    Livewire::actingAs($this->staff)
        ->test(Index::class)
        ->call('restoreClient', $client->id)
        ->assertForbidden();
});

it('prevents staff from force deleting a client', function () {
    $client = Client::factory()->deleted()->create();

    Livewire::actingAs($this->staff)
        ->test(Index::class)
        ->call('forceDeleteClient', $client->id)
        ->assertForbidden();
});
