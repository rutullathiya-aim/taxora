@props(['services', 'user'])

<flux:card>
    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
        <div class="w-full md:w-72">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search services..." icon="magnifying-glass" />
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="status">
                @foreach (\App\Enums\ServiceListStatus::cases() as $option)
                @if ($option !== \App\Enums\ServiceListStatus::Deleted || $user->isAdminOrManager())
                <flux:select.option value="{{ $option->value }}">{{ $option->label() }}</flux:select.option>
                @endif
                @endforeach
            </flux:select>
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="sortBy">
                @foreach(\App\Enums\ServiceSort::options() as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="w-full md:w-auto">
            <flux:select wire:model.live="perPage">
                @foreach (config('taxora.pagination.options', [10, 25, 50, 100]) as $option)
                <flux:select.option value="{{ $option }}">{{ $option }} per page</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="w-full md:w-auto">
            <flux:button wire:click="resetFilters" icon="arrow-path" class="w-full md:w-auto text-zinc-700 dark:text-zinc-300 font-normal">Reset</flux:button>
        </div>

        <div class="w-full md:w-auto md:ml-auto">
            @can('create', \App\Models\Service::class)
            <flux:button variant="primary" wire:click="$dispatch('create-service')" icon="plus" class="w-full md:w-auto">Add New Service</flux:button>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($services as $service)
        <flux:card class="flex flex-col">
            <div class="flex items-start gap-4 mb-4">
                <flux:avatar icon="clipboard-document-list" :name="$service->name" color="auto" size="lg" />
                <div class="min-w-0 flex-1">
                    <flux:link href="{{ route('services.show', $service) }}" wire:navigate class="!text-inherit line-clamp-2">
                        {{ $service->name }}
                    </flux:link>
                    <flux:text class="line-clamp-2 {{ $service->description ? '' : 'text-zinc-500' }}">{{ $service->description ?: 'No description provided.' }}</flux:text>
                </div>
            </div>
            <div class="flex items-center gap-4 mb-5 mt-auto">
                <div class="flex items-center gap-1.5">
                    <flux:icon name="clipboard-document-list" class="size-4" />
                    <flux:text>{{ $service->checklist_items_count . ' ' . Str::plural('item', $service->checklist_items_count) }}</flux:text>
                </div>
                <flux:separator vertical />
                @php($status = $service->listStatus())
                <flux:badge :color="$status->color()" size="sm" inset="top bottom" rounded>&#9679; {{ $status->label() }}</flux:badge>
            </div>
            <flux:separator />
            <div class="flex items-center justify-between gap-5 mt-5">
                @if($service->trashed())
                @can('restore', $service)
                <flux:button variant="primary" size="sm" wire:click="$dispatch('confirm-action', { id: '{{ $service->id }}', eventName: 'restore-service', title: 'Restore Service', description: 'Are you sure you want to restore ' + {{ Js::from($service->name) }} + '?', actionText: 'Restore', actionVariant: 'primary' })" class="flex-1">Restore</flux:button>
                @endcan
                @can('forceDelete', $service)
                <flux:button size="sm" icon="trash" color="danger" wire:click="$dispatch('confirm-action', { id: '{{ $service->id }}', eventName: 'force-delete-service', title: 'Permanently Delete Service', description: 'Are you sure you want to permanently delete ' + {{ Js::from($service->name) }} + '? This action cannot be undone.', actionText: 'Delete Forever', actionVariant: 'danger' })" class="w-12" />
                @endcan
                @else
                @can('view', $service)
                <flux:button variant="primary" size="sm" :href="route('services.show', $service)" wire:navigate class="flex-1">
                    @can('update', $service) Manage @else View @endcan
                </flux:button>
                @endcan
                @can('update', $service)
                <flux:button size="sm" icon="pencil-square" wire:click="$dispatch('edit-service', { id: '{{ $service->id }}' })" class="w-12" />
                @endcan
                @can('delete', $service)
                <flux:button size="sm" icon="trash" color="danger" wire:click="$dispatch('confirm-action', { id: '{{ $service->id }}', eventName: 'delete-service', title: 'Delete Service', description: 'Are you sure you want to delete ' + {{ Js::from($service->name) }} + ' and all its checklists?', actionText: 'Delete', actionVariant: 'danger' })" class="w-12" />
                @endcan
                @endif
            </div>
        </flux:card>

        @empty
        <div class="col-span-full">
            <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-200 py-16 dark:border-zinc-700">
                <flux:heading size="lg">No services found</flux:heading>
            </div>
        </div>
        @endforelse
    </div>

    @if($services->hasPages())
    <div class="mt-6">
        {{ $services->links() }}
    </div>
    @endif
</flux:card>