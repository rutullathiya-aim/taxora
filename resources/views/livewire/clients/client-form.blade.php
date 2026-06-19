<flux:modal wire:model="showModal" flyout class="!p-5 lg:!p-8">
    <form wire:submit="save" class="space-y-6">
        <div class="flex items-center gap-2">
            <flux:icon variant="outline" name="user" class="size-4" />
            <flux:heading size="lg">{{ $client ? 'Edit Client' : 'Add Client' }}</flux:heading>
        </div>
        <flux:separator />
        <flux:input :autofocus="! $client" wire:model.blur="client_name" label="Client Name" placeholder="Enter client name" />
        <flux:input wire:model.blur="company_name" label="Company Name" placeholder="Enter company name" />
        <flux:input type="email" wire:model.blur="email" label="Email" placeholder="Enter email" />
        <flux:input type="tel" wire:model.blur="phone" label="Phone" placeholder="Enter phone number" />
        <flux:textarea wire:model.blur="address" label="Address" placeholder="Enter full address" rows="3" />

        @if (! $client)
        <flux:input wire:model.blur="project_name" label="Project Name" placeholder="Enter project name" />

        <flux:select wire:model="service_id" label="Service" placeholder="Select service...">
            <flux:select.option value="">Select Service</flux:select.option>
            @foreach($this->services as $service)
            <flux:select.option value="{{ $service->id }}">{{ $service->name }}</flux:select.option>
            @endforeach
        </flux:select>

        @endif
        @if ($client)
        <flux:select wire:model="status" label="Client Status">
            @foreach (\App\Enums\ClientStatus::options() as $value => $label)
            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        @endif
        <flux:separator />
        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost" wire:loading.attr="disabled" wire:target="save">Cancel</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary" icon="bookmark-square" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">{{ $client ? 'Update Client' : 'Save Client' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </flux:button>
        </div>
    </form>
</flux:modal>