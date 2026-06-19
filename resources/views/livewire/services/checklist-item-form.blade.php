<flux:modal wire:model="showItemModal" flyout class="!p-5 lg:!p-8">
    <form wire:submit.prevent="save" class="space-y-6">
        <flux:heading size="lg">{{ $item ? 'Edit Checklist Item' : 'Add Checklist Item' }}</flux:heading>
        <flux:separator />

        <flux:textarea wire:model="itemTitle" label="Document Title" placeholder="Document Names" rows="4" />
        <flux:textarea wire:model="itemDescription" label="Description / Guidelines" placeholder="Add description here" />


        <flux:switch wire:model="itemIsActive" label="Active Status" description="Whether this document is actively required" />
        <flux:switch wire:model="itemIsMandatory" label="Mandatory Document" description="Required for compliance completion" />

        <flux:separator />
        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">
                <span wire:loading.remove wire:target="save">{{ $item ? 'Update Item' : 'Save Item' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </flux:button>
        </div>
    </form>
</flux:modal>