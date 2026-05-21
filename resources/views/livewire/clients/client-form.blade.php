<div>
    <flux:modal wire:model="showModal" flyout class="!p-5 lg:!p-8">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingClientId ? 'Edit Client' : 'Add Client' }}</flux:heading>
            </div>
            <flux:separator />
            <flux:input wire:model="client_name" label="Client Name" placeholder="Enter client name" />
            <flux:input wire:model="company_name" label="Company Name" placeholder="Enter company name" />
            <flux:input type="email" wire:model="email" label="Email" placeholder="Enter email" />
            <flux:input type="tel" wire:model="phone" label="Phone" placeholder="Enter phone number" />
            <flux:input wire:model="address" label="Address" placeholder="Enter address" />

            @if(!$editingClientId)
                <flux:input wire:model="project_name" label="Project Name" placeholder="Enter project name" />

                <flux:select wire:model="service_id" label="Service" placeholder="Select service...">
                    <option value="">Select Service</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </flux:select>

                <flux:input type="date" wire:model="due_date" label="Due Date" />
            @endif
            @if($editingClientId)
                <flux:switch wire:model="is_active" label="Active Status"
                    description="Whether this client is currently active" />
            @endif
            <flux:separator />
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="saveClient">
                    <span wire:loading.remove wire:target="saveClient">
                        {{ $editingClientId ? 'Update Client' : 'Save Client' }}
                    </span>
                    <span wire:loading wire:target="saveClient">
                        Saving...
                    </span>
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
