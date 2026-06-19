<div>
    <x-slot:heading>{{ $service->name }}</x-slot:heading>
    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Services' => route('services.index'), $service->name]" />
    </x-slot:breadcrumbs>

    <flux:card class="my-6 relative">
        <div class="flex items-start gap-4 min-w-0">
            <flux:avatar :icon="$service->icon ?: 'briefcase'" color="auto" />
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-4 flex-wrap">
                    <flux:heading size="lg" class="truncate">{{ $service->name }}</flux:heading>
                    <flux:badge :color="$service->status->color()" rounded size="sm">&#9679; {{ $service->status->label() }}</flux:badge>
                </div>
                @if($service->description)
                <flux:text class="mt-2">{{ $service->description }}</flux:text>
                @endif
                <div class="flex items-center gap-2 text-sm !text-zinc-500 dark:!text-white/70 mt-5">
                    <flux:icon name="document-text" class="size-4" /> {{ $this->stats['total'] }} Documents
                </div>
            </div>
        </div>

        @canany(['update', 'delete', 'restore', 'forceDelete'], $service)
        <flux:dropdown align="end" class="absolute top-6 right-6">
            <flux:button size="sm" icon="ellipsis-vertical" variant="ghost" />
            <flux:menu>
                @if($service->trashed())
                @can('restore', $service)
                <flux:menu.item icon="arrow-uturn-left" wire:click="$dispatch('confirm-action', { id: '{{ $service->id }}', eventName: 'restore-service', title: 'Restore Service', description: 'Are you sure you want to restore {{ addslashes($service->name) }}?', actionText: 'Restore' })">Restore</flux:menu.item>
                @endcan
                @can('forceDelete', $service)
                <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-action', { id: '{{ $service->id }}', eventName: 'force-delete-service', title: 'Permanently Delete Service', description: 'Are you sure you want to permanently delete {{ addslashes($service->name) }}? This cannot be undone.', actionText: 'Permanently Delete', actionVariant: 'danger' })">Permanently Delete</flux:menu.item>
                @endcan
                @else
                @can('update', $service)
                <flux:menu.item icon="pencil-square" wire:click="$dispatch('edit-service', { id: '{{ $service->id }}' })">Edit</flux:menu.item>
                @endcan
                @can('delete', $service)
                <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-action', { id: '{{ $service->id }}', eventName: 'delete-service', title: 'Delete Service', description: 'Are you sure you want to delete {{ addslashes($service->name) }}?', actionText: 'Delete', actionVariant: 'danger' })">Delete</flux:menu.item>
                @endcan
                @endif
            </flux:menu>
        </flux:dropdown>
        @endcanany
    </flux:card>

    <x-services.checklist-table :items="$this->items" :service="$service" />

    <livewire:services.checklist-item-form />
    <livewire:services.service-form />
</div>