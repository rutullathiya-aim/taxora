@props(['items', 'service'])

<flux:card>
    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
        <div class="w-full md:w-72">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search checklist items..." icon="magnifying-glass" />
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="filterMandatory">
                <option value="">All Types</option>
                <option value="yes">Required</option>
                <option value="no">Optional</option>
            </flux:select>
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="filterActive">
                <option value="">All Statuses</option>
                <option value="yes">Active</option>
                <option value="no">Inactive</option>
            </flux:select>
        </div>

        <div class="w-full md:w-auto md:ml-auto">
            @can('update', $service)
            <flux:button variant="primary" wire:click="$dispatch('create-item', { serviceId: '{{ $service->id }}' })" icon="plus" class="w-full md:w-auto">Add New Checklist Item</flux:button>
            @endcan
        </div>
    </div>

    <div x-data x-init="if (typeof Sortable !== 'undefined') {
                const container = $el.querySelector('.items-container');
                if (container) {
                    Sortable.create(container, {
                        handle: '.item-drag-handle',
                        animation: 200,
                        ghostClass: 'opacity-30',
                        onEnd: (evt) => {
                            const itemId = evt.item.dataset.id;
                            const newIndex = evt.newIndex;
                            $wire.call('updateItemOrder', itemId, newIndex);
                        }
                    });
                }
            }
        ">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Title</flux:table.column>
                <flux:table.column>Requirement</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column align="center">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows class="items-container">
                @forelse($items as $item)
                <flux:table.row wire:key="item-{{ $item->id }}" data-id="{{ $item->id }}">
                    <flux:table.cell class="flex items-center gap-3">
                        <flux:icon name="grip-vertical" class="item-drag-handle cursor-grab active:cursor-grabbing text-zinc-400" />
                        <div class="flex items-center gap-2">
                            <flux:text class="{{ ! $item->is_active ? 'line-through' : 'font-medium' }}">{!! nl2br(e($item->title)) !!}</flux:text>
                            @if($item->description)
                            <flux:tooltip :content="$item->description">
                                <flux:button icon="information-circle" size="sm" variant="ghost" />
                            </flux:tooltip>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if($item->is_mandatory)
                        <flux:badge color="red" size="sm" rounded>Required</flux:badge>
                        @else
                        <flux:badge color="zinc" size="sm" rounded>Optional</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        @if($item->is_active)
                        <flux:badge color="green" size="sm" rounded>Active</flux:badge>
                        @else
                        <flux:badge color="zinc" size="sm" rounded>Inactive</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="text-center">
                        @can('update', $service)
                        <flux:dropdown align="end">
                            <flux:button size="sm" icon="ellipsis-vertical" variant="ghost" />
                            <flux:menu>
                                <flux:menu.item icon="pencil-square" wire:click="$dispatch('edit-item', { id: '{{ $item->id }}' })">Edit</flux:menu.item>
                                <flux:menu.item icon="copy" wire:click="duplicateItem('{{ $item->id }}')">Duplicate</flux:menu.item>
                                <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-action', { id: '{{ $item->id }}', eventName: 'delete-item', title: 'Delete Checklist Item', description: 'Are you sure you want to delete {{ addslashes($item->title) }}?', actionText: 'Delete', actionVariant: 'danger' })">Delete</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                        @endcan
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-6">No Checklist Items found.</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</flux:card>