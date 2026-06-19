<div>
    <flux:modal name="global-confirm-modal" :closable=false class="md:w-96" wire:close="resetState">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $title }}</flux:heading>
                <flux:text class="mt-2">{{ $description }}</flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button :variant="$actionVariant" x-on:click="Livewire.dispatch($wire.eventName, { id: $wire.modelId }); $flux.modal('global-confirm-modal').close()">{{ $actionText }}</flux:button>
            </div>
        </div>
        <flux:modal.close class="absolute top-0 end-0 mt-4 me-4">
            <flux:button variant="ghost" icon="x-mark" size="sm" class="hover:text-accent transition-all" title="Cancel" />
        </flux:modal.close>
    </flux:modal>
</div>