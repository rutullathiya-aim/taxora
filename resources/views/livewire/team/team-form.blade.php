<flux:modal wire:model="showModal" flyout class="!p-5 lg:!p-8">
    <form wire:submit.prevent="save" class="space-y-6">
        <flux:heading size="lg">{{ $user ? 'Edit Team Member' : 'Add Team Member' }}</flux:heading>
        <flux:separator />

        <flux:input wire:model="name" label="Name" placeholder="Enter Name" />
        <flux:input type="email" wire:model="email" label="Email Address" placeholder="Enter Email Address" />
        <flux:input type="tel" wire:model="phone" label="Phone" placeholder="Enter Phone Number" />

        @if(auth()->user()->isAdmin())
        <flux:select wire:model="role" label="Role">
            @foreach(\App\Enums\UserRole::cases() as $roleOption)
            <flux:select.option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</flux:select.option>
            @endforeach
        </flux:select>
        @endif

        <flux:separator />
        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>

            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">{{ $user ? 'Update Team Member' : 'Save Team Member' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </flux:button>
        </div>
    </form>
</flux:modal>