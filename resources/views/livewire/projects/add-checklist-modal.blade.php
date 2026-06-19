<div>
    <flux:modal wire:model.live="showModal" :closable=false class="md:w-96">
        <div class="space-y-6">
            <flux:heading size="lg">Add Checklist Item</flux:heading>
            <flux:input wire:model="newDocumentName" label="Checklist Item Name" placeholder="Enter Checklist Name" />
            <flux:switch wire:model="newDocumentMandatory" label="Is this document mandatory?" />

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
                <flux:button variant="primary" wire:click="saveNewDocument">Add Checklist</flux:button>
            </div>
        </div>
        <flux:button variant="ghost" icon="x-mark" size="sm" class="!absolute top-0 end-0 mt-4 me-4 hover:text-accent transition-all" title="Cancel" wire:click="$set('showModal', false)" />
    </flux:modal>
</div>
