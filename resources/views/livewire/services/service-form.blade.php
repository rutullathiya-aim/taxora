<flux:modal wire:model="showModal" flyout class="!p-5 lg:!p-8">
    <form wire:submit="save" class="space-y-6">
        <div class="flex items-center gap-2">
            <flux:icon variant="outline" name="clipboard-document-list" class="size-4" />
            <flux:heading size="lg">{{ $service ? 'Edit Service' : 'Add Service' }}</flux:heading>
        </div>
        <flux:separator />

        <flux:input :autofocus="! $service" wire:model.blur="name" label="Service Name" placeholder="Enter service name" />
        <flux:textarea wire:model.blur="description" label="Description" placeholder="Enter service description" rows="2" />

        @if ($service)
        <flux:select wire:model="status" label="Service Status">
            @foreach (\App\Enums\ServiceStatus::options() as $value => $label)
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
                <span wire:loading.remove wire:target="save">{{ $service ? 'Update Service' : 'Save Service' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </flux:button>
        </div>
    </form>
</flux:modal>