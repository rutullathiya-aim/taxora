<?php

use App\Livewire\Projects\ProjectForm;
use App\Models\Client;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create();
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
        'status' => 'in_progress',
    ]);

    $this->actingAs($this->admin);

    // 1. When creating a project (editingProjectId is null)
    Livewire::test(ProjectForm::class)
        ->assertDontSeeHtml('wire:model="status"')
        // 2. When editing a project (editingProjectId is set via editProject)
        ->call('editProject', $project->id)
        ->assertSeeHtml('wire:model="status"');
});
