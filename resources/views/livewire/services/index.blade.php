<div>
    <x-slot:heading>Services</x-slot:heading>

    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Services']" />
    </x-slot:breadcrumbs>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5 my-6">
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="users" color="blue" circle />
                <div>
                    <flux:heading>Total Services</flux:heading>
                    <flux:text class="font-medium">{{ $totalServices }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="check" color="green" circle />
                <div>
                    <flux:heading>Active Services</flux:heading>
                    <flux:text class="font-medium">{{ $activeServices }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="x-mark" color="red" circle />
                <div>
                    <flux:heading>Inactive Services</flux:heading>
                    <flux:text class="font-medium">{{ $inactiveServices }}</flux:text>
                </div>
            </div>
        </flux:card>
    </div>

    <flux:card>
        <div class="flex flex-col md:flex-row md:items-center gap-4">
            <div class="w-full md:w-72">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search services..."
                    icon="magnifying-glass" />
            </div>

            <div class="w-full md:w-72">
                <flux:select wire:model.live="statusFilter">
                    <flux:select.option value="all">Status: All</flux:select.option>
                    <flux:select.option value="active">Status: Active</flux:select.option>
                    <flux:select.option value="inactive">Status: Inactive</flux:select.option>
                </flux:select>
            </div>

            <div class="w-full md:w-72">
                <flux:select wire:model.live="sortBy">
                    <flux:select.option value="latest">Sort: Latest</flux:select.option>
                    <flux:select.option value="oldest">Sort: Oldest</flux:select.option>
                    <flux:select.option value="a_to_z">Sort: A to Z</flux:select.option>
                    <flux:select.option value="z_to_a">Sort: Z to A</flux:select.option>
                </flux:select>
            </div>
            <div class="w-full md:w-auto md:ml-auto">
                <flux:button variant="primary" wire:click="createService" icon="plus" class="w-full md:w-auto">Add
                    New Service
                </flux:button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5 mt-6">
            @forelse($services as $service)
                <flux:card class="relative flex flex-col">
                    <div class="flex items-start gap-4 mb-4">
                        <flux:avatar :icon="$service->icon ?: 'document-text'" color="auto" size="lg" />
                        <div class="min-w-0 flex-1 space-y-2">
                            <flux:heading>{{ $service->name }}</flux:heading>
                            <flux:text class="line-clamp-2">{{ $service->description ?: 'No description provided.' }}
                            </flux:text>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 mb-5 text-sm text-zinc-500 dark:text-zinc-400 mt-auto">
                        <div class="flex items-center gap-1.5">
                            <flux:icon name="clipboard-document-list" class="size-4" />
                            <span>{{ $service->checklist_items_count }}
                                {{ Str::plural('item', $service->checklist_items_count) }}</span>
                        </div>
                        <flux:separator vertical />
                        @if($service->status === 'active')
                            <flux:badge color="green" size="sm">Active</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">Inactive</flux:badge>
                        @endif
                    </div>
                    <flux:separator />
                    <div class="flex items-center justify-between gap-5 mt-5">
                        <flux:button variant="primary" size="sm" :href="route('services.show', $service)"
                            wire:navigate class="flex-1">
                            Manage</flux:button>
                        <flux:button size="sm" icon="pencil-square" wire:click="editService('{{ $service->id }}')"
                            class="w-12" />
                        <flux:button size="sm" icon="trash" color="danger"
                            wire:click="deleteService('{{ $service->id }}')"
                            wire:confirm="Are you sure you want to delete this service and all its checklists?"
                            class="w-12" />
                    </div>
                </flux:card>

            @empty
                <div class="col-span-full">
                    <div
                        class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-200 py-16 dark:border-zinc-700">
                        <flux:heading size="lg" class="text-zinc-500">No services found</flux:heading>
                    </div>
                </div>
            @endforelse
        </div>
    </flux:card>

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showCreateModal" flyout>
        <div class="space-y-6">
            <flux:heading size="lg">{{ $editingServiceId ? 'Edit Service' : 'Add Service' }}</flux:heading>

            <flux:input wire:model="name" label="Service Name" placeholder="e.g. RERA Registration" />

            <flux:textarea wire:model="description" label="Description"
                placeholder="Brief description of this service type" />

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="icon" label="Icon Name" placeholder="e.g. briefcase, shield-check"
                    description="Heroicons name" />

                <flux:select wire:model="status" label="Status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </flux:select>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="saveService" wire:loading.attr="disabled">
                    {{ $editingServiceId ? 'Update Service' : 'Save Service' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>