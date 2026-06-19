@props(['clients', 'user'])

<flux:card>
    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
        <div class="w-full md:w-72">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search clients..." icon="magnifying-glass" />
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="status">
                @foreach (\App\Enums\ClientListStatus::cases() as $option)
                @if ($option !== \App\Enums\ClientListStatus::Deleted || $user->isAdminOrManager())
                <flux:select.option value="{{ $option->value }}">{{ $option->label() }}</flux:select.option>
                @endif
                @endforeach
            </flux:select>
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="sortBy">
                @foreach(\App\Enums\ClientSort::options() as $value => $label)
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
            @can('create', \App\Models\Client::class)
            <flux:button variant="primary" wire:click="$dispatch('create-client')" icon="plus" class="w-full md:w-auto">Add New Client</flux:button>
            @endcan
        </div>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>#</flux:table.column>
            <flux:table.column>Client</flux:table.column>
            <flux:table.column>Company</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Phone</flux:table.column>
            <flux:table.column>Created On</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="center">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($clients as $client)
            <flux:table.row>
                <flux:table.cell>{{ \App\Support\TableSupport::rowNumber($clients, $loop->index) }}</flux:table.cell>
                <flux:table.cell>
                    <flux:link href="{{ route('clients.show', $client) }}" wire:navigate>
                        <flux:avatar circle size="sm" name="{{ $client->client_name }}" color="auto" />{{ $client->client_name }}
                    </flux:link>
                </flux:table.cell>
                <flux:table.cell>
                    <div class="flex items-center gap-2">
                        @if($client->company_name)
                        <flux:icon name="building-office" variant="outline" class="size-4" />{{ $client->company_name }}
                        @else
                        <flux:icon name="user" variant="outline" class="size-4" />Individual
                        @endif
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:link href="mailto:{{ $client->email }}" target="_blank">
                        <flux:icon name="envelope" variant="outline" class="size-4" />{{ $client->email }}
                    </flux:link>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:link href="tel:{{ $client->phone }}">
                        <flux:icon name="phone" variant="outline" class="size-4" />{{ $client->phone }}
                    </flux:link>
                </flux:table.cell>
                <flux:table.cell>
                    <div class="flex items-center gap-2">
                        <flux:icon name="calendar-days" variant="outline" class="size-4" />{{ $client->created_at->isoFormat('DD MMM YYYY') }}
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    @php($status = $client->listStatus())
                    <flux:badge :color="$status->color()" size="sm" inset="top bottom" rounded>&#9679; {{ $status->label() }}</flux:badge>
                </flux:table.cell>
                <flux:table.cell class="text-center">
                    <flux:dropdown align="end">
                        <flux:button size="sm" icon="ellipsis-vertical" />
                        <flux:menu>
                            @can('view', $client)
                            <flux:menu.item icon="eye" href="{{ route('clients.show', $client) }}" wire:navigate>View</flux:menu.item>
                            @endcan
                            @if($client->trashed())
                            @can('restore', $client)
                            <flux:menu.item icon="arrow-uturn-left" wire:click="$dispatch('confirm-action', { id: '{{ $client->id }}', eventName: 'restore-client', title: 'Restore Client', description: 'Are you sure you want to restore ' + {{ Js::from($client->client_name) }} + '?', actionText: 'Restore', actionVariant: 'primary' })">Restore</flux:menu.item>
                            @endcan
                            @can('forceDelete', $client)
                            <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-action', { id: '{{ $client->id }}', eventName: 'force-delete-client', title: 'Delete Client Forever', description: 'Are you sure you want to permanently delete ' + {{ Js::from($client->client_name) }} + '? This cannot be undone.', actionText: 'Delete Forever', actionVariant: 'danger' })">Delete Forever</flux:menu.item>
                            @endcan
                            @else
                            @can('update', $client)
                            <flux:menu.item icon="pencil-square" wire:click="$dispatch('edit-client', { id: '{{ $client->id }}' })">Edit</flux:menu.item>
                            @endcan
                            @can('delete', $client)
                            <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-action', { id: '{{ $client->id }}', eventName: 'delete-client', title: 'Delete Client', description: 'Are you sure you want to delete ' + {{ Js::from($client->client_name) }} + '?', actionText: 'Delete', actionVariant: 'danger' })">Delete</flux:menu.item>
                            @endcan
                            @endif
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="8" class="text-center py-6">No clients found</flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    @if($clients->hasPages())
    <div class="mt-6">
        {{ $clients->links() }}
    </div>
    @endif
</flux:card>