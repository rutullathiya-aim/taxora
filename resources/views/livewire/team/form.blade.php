<div>
    <flux:modal wire:model="showModal" flyout>
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $userId ? 'Edit Team Member' : 'Add Team Member' }}</flux:heading>

            <flux:input wire:model="name" label="Name" placeholder="Enter Name" />
            <flux:input type="email" wire:model="email" label="Email Address" placeholder="Enter Email Address" />
            <flux:input wire:model="phone" label="Phone" placeholder="Enter Phone Number" />

            <flux:select wire:model="role" label="Role">
                @foreach(\App\Enums\UserRole::cases() as $roleOption)
                    <flux:select.option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    {{ $userId ? 'Save Changes' : 'Create Member' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>