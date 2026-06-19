<?php

use App\Enums\ServiceChecklistItemStatus;
use App\Enums\ServiceStatus;
use App\Livewire\Projects\ProjectForm;
use App\Livewire\Services\ChecklistItemForm;
use App\Livewire\Services\Show as ServiceShow;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectChecklist;
use App\Models\Service;
use App\Models\ServiceChecklistItem;
use App\Models\User;
use App\Services\ServiceManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('creates a service with auto-generated slug', function () {
    $manager = app(ServiceManager::class);

    $service = $manager->create([
        'name' => 'RERA Registration',
        'description' => 'RERA service process',
        'icon' => 'building-office',
        'status' => ServiceStatus::Active->value,
    ]);

    expect($service)->toBeInstanceOf(Service::class)
        ->and($service->name)->toBe('RERA Registration')
        ->and($service->slug)->toBe('rera-registration')
        ->and($service->status->value)->toBe(ServiceStatus::Active->value);
});

it('creates checklist items directly under service', function () {
    $service = Service::create(['name' => 'GST Filing', 'slug' => 'gst-filing', 'status' => ServiceStatus::Active->value]);

    ServiceChecklistItem::create([
        'service_id' => $service->id,
        'title' => 'PAN Card',
        'is_mandatory' => true,
        'sort_order' => 0,
        'status' => ServiceChecklistItemStatus::Active->value,
    ]);

    ServiceChecklistItem::create([
        'service_id' => $service->id,
        'title' => 'Address Proof',
        'is_mandatory' => true,
        'sort_order' => 1,
        'status' => ServiceChecklistItemStatus::Active->value,
    ]);

    expect($service->checklistItems)->toHaveCount(2)
        ->and($service->checklistItems->first()->title)->toBe('PAN Card')
        ->and($service->checklistItems->last()->title)->toBe('Address Proof');
});

it('supports custom sorting of service checklist items', function () {
    $service = Service::create(['name' => 'ITR', 'slug' => 'itr', 'status' => ServiceStatus::Active->value]);

    ServiceChecklistItem::create(['service_id' => $service->id, 'title' => 'PAN Card', 'sort_order' => 0, 'status' => ServiceChecklistItemStatus::Active->value]);
    ServiceChecklistItem::create(['service_id' => $service->id, 'title' => 'Form 16', 'sort_order' => 1, 'status' => ServiceChecklistItemStatus::Active->value]);
    ServiceChecklistItem::create(['service_id' => $service->id, 'title' => 'Bank Statement', 'sort_order' => 2, 'status' => ServiceChecklistItemStatus::Active->value]);

    $items = $service->checklistItems()->orderBy('sort_order')->pluck('title')->toArray();
    expect($items)->toBe(['PAN Card', 'Form 16', 'Bank Statement']);
});

it('auto-assigns active checklist items to project on creation', function () {
    $service = Service::create(['name' => 'Audit', 'slug' => 'audit', 'status' => ServiceStatus::Active->value]);

    ServiceChecklistItem::create(['service_id' => $service->id, 'title' => 'Trial Balance', 'is_mandatory' => true, 'sort_order' => 0, 'status' => ServiceChecklistItemStatus::Active->value]);
    ServiceChecklistItem::create(['service_id' => $service->id, 'title' => 'BRS', 'is_mandatory' => true, 'sort_order' => 1, 'status' => ServiceChecklistItemStatus::Active->value]);
    ServiceChecklistItem::create(['service_id' => $service->id, 'title' => 'Inactive Item', 'is_mandatory' => false, 'sort_order' => 2, 'status' => ServiceChecklistItemStatus::Inactive->value]);

    $client = Client::create([
        'client_name' => 'Test Client',
        'email' => 'test@example.com',
        'phone' => '1234567890',
        'company_name' => 'Test Corp',
        'address' => '123 Test Street',
    ]);

    $this->admin->assignRole('admin');
    Livewire::actingAs($this->admin);
    Livewire::test(ProjectForm::class)
        ->set('client_id', $client->id)
        ->set('project_name', 'Audit 2026')
        ->set('service_id', $service->id)
        ->set('status', 'active')
        ->call('save')
        ->assertHasNoErrors()
        ->assertStatus(200);

    $project = Project::where('project_name', 'Audit 2026')->first();

    expect($project->checklists)->toHaveCount(2)
        ->and($project->checklists->pluck('name')->toArray())->toBe(['Trial Balance', 'BRS'])
        ->and($project->checklists->every(fn ($c) => $c->status->value === 'Pending'))->toBeTrue();
});

it('keeps project checklists independent from master items', function () {
    $service = Service::create(['name' => 'Trade License', 'slug' => 'trade', 'status' => ServiceStatus::Active->value]);
    $item = ServiceChecklistItem::create(['service_id' => $service->id, 'title' => 'Application Form', 'is_mandatory' => true, 'sort_order' => 0, 'status' => ServiceChecklistItemStatus::Active->value]);

    $client = Client::create([
        'client_name' => 'Trade Client',
        'email' => 'trade@example.com',
        'phone' => '9876543210',
        'company_name' => 'Trade Corp',
        'address' => '456 Trade Road',
    ]);

    $project = Project::create([
        'client_id' => $client->id,
        'project_name' => 'Trade 2026',
        'service_id' => $service->id,
        'status' => 'active',
    ]);

    $projectChecklist = ProjectChecklist::create([
        'project_id' => $project->id,
        'name' => $item->title,
        'is_mandatory' => $item->is_mandatory,
        'status' => 'Pending',
    ]);

    $item->update(['title' => 'Updated Application Form']);

    $projectChecklist->refresh();
    expect($projectChecklist->name)->toBe('Application Form');
});

it('cascades delete from service to checklist items', function () {
    $service = Service::create(['name' => 'Cascade Test', 'slug' => 'cascade-test', 'status' => ServiceStatus::Active->value]);
    ServiceChecklistItem::create(['service_id' => $service->id, 'title' => 'Item 1', 'sort_order' => 0, 'status' => ServiceChecklistItemStatus::Active->value]);
    ServiceChecklistItem::create(['service_id' => $service->id, 'title' => 'Item 2', 'sort_order' => 1, 'status' => ServiceChecklistItemStatus::Active->value]);

    expect(ServiceChecklistItem::count())->toBe(2);

    $service->forceDelete();

    expect(ServiceChecklistItem::count())->toBe(0);
});

it('allows creating a checklist item via livewire component', function () {
    $service = Service::create(['name' => 'RERA Registration', 'slug' => 'rera-registration', 'status' => ServiceStatus::Active->value]);

    Livewire::actingAs($this->admin);

    Livewire::test(ChecklistItemForm::class, ['serviceId' => $service->id])
        ->call('openCreateModal', $service->id)
        ->set('itemTitle', 'PAN Card')
        ->set('itemIsMandatory', true)
        ->set('status', ServiceChecklistItemStatus::Active->value)
        ->call('save');

    $item = ServiceChecklistItem::first();

    expect($item)->not->toBeNull()
        ->and($item->title)->toBe('PAN Card')
        ->and($item->is_mandatory)->toBeTrue()
        ->and($item->status->value)->toBe(ServiceChecklistItemStatus::Active->value);
});

it('allows editing a checklist item via livewire component', function () {
    $service = Service::create(['name' => 'RERA Registration', 'slug' => 'rera-registration', 'status' => ServiceStatus::Active->value]);
    $item = ServiceChecklistItem::create([
        'service_id' => $service->id,
        'title' => 'PAN Card',
        'is_mandatory' => true,
        'status' => ServiceChecklistItemStatus::Active->value,
    ]);

    Livewire::actingAs($this->admin);

    Livewire::test(ChecklistItemForm::class, ['serviceId' => $service->id])
        ->call('openEditModal', $item->id)
        ->set('itemTitle', 'Updated PAN Card')
        ->set('itemIsMandatory', false)
        ->call('save');

    $item->refresh();

    expect($item->title)->toBe('Updated PAN Card')
        ->and($item->is_mandatory)->toBeFalse();
});

it('allows deleting a checklist item via livewire component', function () {
    $service = Service::create(['name' => 'RERA Registration', 'slug' => 'rera-registration', 'status' => ServiceStatus::Active->value]);
    $item = ServiceChecklistItem::create([
        'service_id' => $service->id,
        'title' => 'PAN Card',
        'is_mandatory' => true,
        'status' => ServiceChecklistItemStatus::Active->value,
    ]);

    Livewire::actingAs($this->admin);

    Livewire::test(ServiceShow::class, ['service' => $service])
        ->call('deleteItem', $item->id);

    expect(ServiceChecklistItem::find($item->id))->toBeNull();
});

it('allows duplicating a checklist item via livewire component', function () {
    $service = Service::create(['name' => 'RERA Registration', 'slug' => 'rera-registration', 'status' => ServiceStatus::Active->value]);
    $item = ServiceChecklistItem::create([
        'service_id' => $service->id,
        'title' => 'PAN Card',
        'is_mandatory' => true,
        'status' => ServiceChecklistItemStatus::Active->value,
    ]);

    Livewire::actingAs($this->admin);

    Livewire::test(ServiceShow::class, ['service' => $service])
        ->call('duplicateItem', $item->id);

    $duplicate = ServiceChecklistItem::where('title', 'PAN Card (Copy)')->first();

    expect($duplicate)->not->toBeNull()
        ->and($duplicate->is_mandatory)->toBeTrue();
});

it('allows toggling mandatory and status inline', function () {
    $service = Service::create(['name' => 'RERA Registration', 'slug' => 'rera-registration', 'status' => ServiceStatus::Active->value]);
    $item = ServiceChecklistItem::create([
        'service_id' => $service->id,
        'title' => 'PAN Card',
        'is_mandatory' => true,
        'status' => ServiceChecklistItemStatus::Active->value,
    ]);

    Livewire::actingAs($this->admin);

    $component = Livewire::test(ServiceShow::class, ['service' => $service]);

    $component->call('toggleMandatory', $item->id);
    expect($item->refresh()->is_mandatory)->toBeFalse();

    $component->call('toggleActive', $item->id);
    expect($item->refresh()->status->value)->toBe(ServiceChecklistItemStatus::Inactive->value);
});

it('allows custom drag-and-drop reordering', function () {
    $service = Service::create(['name' => 'RERA Registration', 'slug' => 'rera-registration', 'status' => ServiceStatus::Active->value]);
    $item1 = ServiceChecklistItem::create([
        'service_id' => $service->id,
        'title' => 'PAN Card',
        'sort_order' => 0,
        'status' => ServiceChecklistItemStatus::Active->value,
    ]);
    $item2 = ServiceChecklistItem::create([
        'service_id' => $service->id,
        'title' => 'Aadhaar Card',
        'sort_order' => 1,
        'status' => ServiceChecklistItemStatus::Active->value,
    ]);
    $item3 = ServiceChecklistItem::create([
        'service_id' => $service->id,
        'title' => 'Approved Building Plan',
        'sort_order' => 2,
        'status' => ServiceChecklistItemStatus::Active->value,
    ]);

    Livewire::actingAs($this->admin);

    Livewire::test(ServiceShow::class, ['service' => $service])
        ->call('updateItemOrder', $item2->id, 0);

    $item2->refresh();

    $sortedItems = $service->fresh()->checklistItems()->orderBy('sort_order')->get();

    expect($sortedItems[0]->title)->toBe('Aadhaar Card')
        ->and($sortedItems[1]->title)->toBe('PAN Card')
        ->and($sortedItems[2]->title)->toBe('Approved Building Plan');
});

it('stores all valid statuses on project checklists', function (string $status) {
    $service = Service::create(['name' => 'Status Test ' . $status, 'slug' => 'status-' . strtolower($status), 'status' => ServiceStatus::Active->value]);

    $client = Client::create([
        'client_name' => 'Status Client ' . $status,
        'email' => "status-{$status}@example.com",
        'phone' => '1112223333',
        'company_name' => 'Status Corp',
        'address' => '101 Status Lane',
    ]);

    $project = Project::create([
        'client_id' => $client->id,
        'project_name' => 'Status ' . $status,
        'service_id' => $service->id,
        'status' => 'active',
    ]);

    $checklist = ProjectChecklist::create([
        'project_id' => $project->id,
        'name' => 'Test Document',
        'is_mandatory' => true,
        'status' => $status,
    ]);

    expect($checklist->status->value)->toBe($status);
})->with([
    'Pending',
    'Submitted',
    'Approved',
    'Rejected',
    'Not Applicable',
]);
