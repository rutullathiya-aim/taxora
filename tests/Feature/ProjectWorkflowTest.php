<?php

use App\Enums\ServiceStatus;
use App\Livewire\Projects\ProjectForm;
use App\Models\Client;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('hides status field when creating a project but shows it when editing', function () {
    $client = Client::create([
        'client_name' => 'Test Client',
        'email' => 'test@example.com',
        'phone' => '1234567890',
        'company_name' => 'Test Corp',
        'address' => '123 Test Street',
    ]);

    $service = Service::create([
        'name' => 'Test Service',
        'slug' => 'test-service',
        'status' => 'active',
    ]);

    $project = Project::create([
        'client_id' => $client->id,
        'project_name' => 'Test Project',
        'service_id' => $service->id,
        'status' => 'active',
    ]);

    $this->actingAs($this->admin);

    // 1. When creating a project (editingProjectId is null)
    Livewire::test(ProjectForm::class)
        ->assertDontSeeHtml('wire:model="status"')
        // 2. When editing a project (editingProjectId is set via openEditModal)
        ->call('openEditModal', $project->id)
        ->assertSeeHtml('wire:model="status"');
});

// --- Form Reset ---

it('retains form data when reopening create modal', function () {
    Livewire::actingAs($this->admin)
        ->test(ProjectForm::class)
        ->set('project_name', 'Typed Project Name')
        ->call('openCreateModal')
        ->assertSet('project_name', 'Typed Project Name')
        ->assertSet('showModal', true);
});

it('resets form when switching from edit to create', function () {
    $client = Client::create([
        'client_name' => 'Test Client',
        'email' => 'test@example.com',
        'phone' => '1234567890',
        'address' => '123 Test Street',
    ]);

    $service = Service::create([
        'name' => 'Test Service',
        'slug' => 'test-service',
        'status' => ServiceStatus::Active->value,
    ]);

    $project = Project::create([
        'client_id' => $client->id,
        'project_name' => 'Existing Project',
        'service_id' => $service->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(ProjectForm::class)
        ->call('openEditModal', $project->id)
        ->call('openCreateModal')
        ->assertSet('project_name', '')
        ->assertSet('editingProjectId', null)
        ->assertSet('showModal', true);
});
