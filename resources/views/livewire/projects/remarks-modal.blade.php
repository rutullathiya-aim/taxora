<div>
    <flux:modal wire:model.live="showModal" :closable=false class="md:w-96">
        <div class="space-y-6">
            <flux:heading size="lg">Remarks</flux:heading>

            <flux:textarea wire:model="remarks" label="Add notes or remarks" placeholder="Enter any remarks for this checklist item..." rows="4" />

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
                <flux:button variant="primary" wire:click="saveRemarks" wire:loading.attr="disabled">Save Remarks</flux:button>
            </div>
        </div>
        <flux:button variant="ghost" icon="x-mark" size="sm" class="!absolute top-0 end-0 mt-4 me-4 hover:text-accent transition-all" title="Cancel" wire:click="$set('showModal', false)" />
    </flux:modal>
</div>
