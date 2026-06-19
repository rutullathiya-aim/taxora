<flux:modal wire:model="showRenameModal" :closable=false class="md:w-[30rem]">
    <form wire:submit="saveDocumentName" class="space-y-6">
        <flux:heading size="lg">Rename Document</flux:heading>
        <flux:input wire:model="renameDocumentName" label="Document Name" placeholder="File Name" />

        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">Save Changes</flux:button>
        </div>
    </form>
    <flux:modal.close class="absolute top-0 end-0 mt-4 me-4">
        <flux:button variant="ghost" icon="x-mark" size="sm" class="hover:text-accent transition-all" title="Cancel" />
    </flux:modal.close>
</flux:modal>
