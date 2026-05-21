<div>
    <flux:modal wire:model="showModal" flyout class="!p-5 lg:!p-8">
        <div class="space-y-6">
            <flux:heading size="lg">{{ $editingProjectId ? 'Edit Project' : 'Add Project' }}</flux:heading>
            <flux:separator />

            <flux:select wire:model="client_id" label="Client">
                <option value="">Select Client</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                @endforeach
            </flux:select>

            <flux:input wire:model="project_name" label="Project Name" placeholder="Enter project name" />

            <flux:select wire:model="service_id" label="Service" :disabled="(bool)$editingProjectId">
                <option value="">Select Service</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                @endforeach
            </flux:select>

            @if ($editingProjectId)
                <flux:select wire:model="status" label="Status">
                    <option value="in_progress">In Progress</option>
                    <option value="on_hold">On Hold</option>
                    <option value="completed">Completed</option>
                </flux:select>
            @endif

            <flux:input type="date" wire:model="due_date" label="Due Date" />

            <flux:separator />
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" wire:click="saveProject">
                    <span wire:loading.remove wire:target="saveProject">
                        {{ $editingProjectId ? 'Update Project' : 'Save Project' }}
                    </span>
                    <span wire:loading wire:target="saveProject">
                        Saving...
                    </span>
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>