<div>
    <x-slot:heading>Clients</x-slot:heading>

    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Clients']" />
    </x-slot:breadcrumbs>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5 my-6">
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="users" color="blue" circle />
                <div>
                    <flux:heading>Total Clients</flux:heading>
                    <flux:text class="font-medium">{{ $totalClients }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="check" color="green" circle />
                <div>
                    <flux:heading>Active Clients</flux:heading>
                    <flux:text class="font-medium">{{ $activeClients }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="x-mark" color="red" circle />
                <div>
                    <flux:heading>Inactive Clients</flux:heading>
                    <flux:text class="font-medium">{{ $inactiveClients }}</flux:text>
                </div>
            </div>
        </flux:card>
    </div>

    <flux:card>
        <div class="flex flex-col md:flex-row md:items-center gap-4">
            <div class="w-full md:w-72">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search clients..."
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
                <flux:button variant="primary" wire:click="$dispatch('create-client')" icon="plus"
                    class="w-full md:w-auto">Add New
                    Client
                </flux:button>
            </div>
        </div>

        <flux:table class="mt-6">
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>Client</flux:table.column>
                <flux:table.column>Company</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Phone</flux:table.column>
                <flux:table.column>Created</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column align="center">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($clients as $index => $client)
                    <flux:table.row>
                        <flux:table.cell>
                            {{ ($clients->currentPage() - 1) * $clients->perPage() + $loop->iteration }}
                        </flux:table.cell>
                        <flux:table.cell class="font-medium">
                            <a href="{{ route('clients.show', $client->id) }}" wire:navigate
                                class="flex items-center gap-2 hover:text-accent transition-colors">
                                <flux:avatar circle size="sm" name="{{ $client->client_name }}" color="auto" />
                                {{ $client->client_name }}
                            </a>
                        </flux:table.cell>
                        <flux:table.cell>{{ $client->company_name }}</flux:table.cell>
                        <flux:table.cell>{{ $client->email }}</flux:table.cell>
                        <flux:table.cell>{{ $client->phone }}</flux:table.cell>
                        <flux:table.cell>{{ $client->created_at->format('d M Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$client->is_active ? 'green' : 'zinc'" size="sm" inset="top bottom" rounded>
                                {{ $client->is_active ? 'Active' : 'Inactive' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="text-center">
                            <flux:dropdown align="end">
                                <flux:button size="sm" icon="ellipsis-vertical" variant="ghost" />

                                <flux:menu>
                                    <flux:menu.item icon="eye" href="{{ route('clients.show', $client->id) }}"
                                        wire:navigate>View</flux:menu.item>
                                    <flux:menu.item icon="pencil-square"
                                        wire:click="$dispatch('edit-client', { id: '{{ $client->id }}' })">Edit
                                    </flux:menu.item>
                                    <flux:menu.item variant="danger" icon="trash"
                                        wire:click="deleteClient('{{ $client->id }}')"
                                        wire:confirm="Are you sure you want to delete this client?">Delete
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center text-zinc-500 py-6">No clients found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if($clients->hasPages())
            <div class="mt-4">
                {{ $clients->links() }}
            </div>
        @endif
    </flux:card>

    <livewire:clients.client-form />
</div>