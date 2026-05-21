<div class="flex flex-col gap-6">
    <x-auth-header title="Set up your password"
        description="Welcome to Taxora! Please set up a secure password to activate your account." />

    <form wire:submit="save" class="flex flex-col gap-6">
        <flux:input type="password" wire:model="password" label="Password" placeholder="Enter a secure password"
            required autofocus />

        <flux:input type="password" wire:model="password_confirmation" label="Confirm Password"
            placeholder="Confirm your password" required />

        <flux:button type="submit" variant="primary" class="w-full">
            Activate Account
        </flux:button>
    </form>
</div>