<div>
    <x-slot:heading>{{ $service->name }}</x-slot:heading>
    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Services' => route('services.index'), $service->name]" />
    </x-slot:breadcrumbs>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5 my-6">
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="document-text" color="blue" circle />
                <div>
                    <flux:heading>Total Documents</flux:heading>
                    <flux:text class="font-medium">{{ $totalItems }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="check" color="green" circle />
                <div>
                    <flux:heading>Active Documents</flux:heading>
                    <flux:text class="font-medium">{{ $activeItemsCount }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="x-mark" color="red" circle />
                <div>
                    <flux:heading>Mandatory Documents</flux:heading>
                    <flux:text class="font-medium">{{ $mandatoryItemsCount }}</flux:text>
                </div>
            </div>
        </flux:card>
    </div>

    <flux:card>
        <div class="flex flex-col md:flex-row md:items-center gap-4">
            <div class="w-full md:w-72">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search checklist items..."
                    icon="magnifying-glass" />
            </div>

            <div class="w-full md:w-72">
                <flux:select wire:model.live="filterMandatory">
                    <option value="">All Types</option>
                    <option value="yes">Required</option>
                    <option value="no">Optional</option>
                </flux:select>
            </div>

            <div class="w-full md:w-72">
                <flux:select wire:model.live="filterStatus">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </flux:select>
            </div>

            <div class="w-full md:w-auto md:ml-auto">
                <flux:button variant="primary" wire:click="createItem" icon="plus" class="w-full md:w-auto">Add New
                    Checklist Item</flux:button>
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
            " class="mt-6">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Title</flux:table.column>
                    <flux:table.column>Requirement</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Allowed File Types</flux:table.column>
                    <flux:table.column class="justify-items-center">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows class="items-container">
                    @forelse($items as $item)
                        <flux:table.row wire:key="item-{{ $item->id }}" data-id="{{ $item->id }}">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:icon name="grip-vertical"
                                    class="item-drag-handle cursor-grab active:cursor-grabbing text-zinc-400" />
                                <div class="flex items-center gap-2">
                                    <span
                                        class="{{ $item->status === 'inactive' ? 'line-through text-zinc-500' : 'font-medium' }}">
                                        {!! nl2br(e($item->title)) !!}
                                    </span>
                                    @if($item->description)
                                        <flux:tooltip :content="$item->description">
                                            <flux:button icon="information-circle" size="sm" variant="ghost"
                                                class="text-zinc-400" />
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
                                @if($item->status === 'active')
                                    <flux:badge color="green" size="sm" rounded>Active</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm" rounded>Inactive</flux:badge>
                                @endif
                                </flux:cell>

                                <flux:table.cell>
                                    @if($item->allowed_file_types)
                                        <div class="flex flex-wrap items-center gap-1">
                                            @foreach(explode(',', $item->allowed_file_types) as $ext)
                                                <flux:badge size="sm">{{ trim($ext) }}</flux:badge>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                    </flux:cell>

                                    <flux:table.cell>
                                        <div class="flex items-center gap-4 justify-center">
                                            <flux:button size="xs" icon="pencil-square"
                                                wire:click="editItem('{{ $item->id }}')" title="Edit document"
                                                class="w-10 p-4" />
                                            <flux:button size="xs" icon="copy" wire:click="duplicateItem('{{ $item->id }}')"
                                                title="Duplicate item" class="w-10 p-4" />
                                            <flux:button size="xs" icon="trash" wire:click="deleteItem('{{ $item->id }}')"
                                                wire:confirm="Are you sure you want to delete this document checklist requirement?"
                                                title="Delete item" class="w-10 p-4 !text-red-500" />
                                        </div>
                                    </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center py-6">No Checklist Items found.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>

    {{-- Create/Edit Checklist Item Modal --}}
    <flux:modal wire:model="showItemModal" flyout>
        <div class="space-y-6">
            <flux:heading size="lg">{{ $editingItemId ? 'Edit Checklist Item' : 'Add Checklist Item' }}</flux:heading>

            <flux:textarea wire:model="itemTitle" label="Document Title" placeholder="e.g. Aadhaar Card of Promoter" rows="4" />

            <flux:textarea wire:model="itemDescription" label="Description / Guidelines"
                placeholder="Describe what the client needs to supply for this document requirement" />

            <flux:input wire:model="itemAllowedFileTypes" label="Allowed Extensions" placeholder="pdf,jpg,png"
                description="Comma-separated extensions" />

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="itemStatus" label="Item Status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </flux:select>

                <div class="flex items-center h-full pt-6">
                    <flux:switch wire:model="itemIsMandatory" label="Mandatory Document"
                        description="Required for compliance completion" />
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="saveItem" wire:loading.attr="disabled">
                    {{ $editingItemId ? 'Update Item' : 'Save Item' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>