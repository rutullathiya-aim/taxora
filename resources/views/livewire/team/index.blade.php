<div>
    <x-slot:heading>Team Members</x-slot:heading>

    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Team']" />
    </x-slot:breadcrumbs>

    <x-team.table :users="$users" />

    <livewire:team.form />

    <flux:modal wire:model="showDeleteModal">
        <form wire:submit="delete" class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Team Member?</flux:heading>
                <flux:text class="mt-2 text-sm text-zinc-500">
                    The team member will be moved to Trash and lose access to Taxora.They can be restored later if
                    needed.
                </flux:text>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="danger" wire:loading.attr="disabled">
                    Yes, Delete
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>