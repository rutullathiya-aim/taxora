<div>
    <x-slot:heading>{{ $client->client_name }}</x-slot:heading>
    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Clients' => route('clients.index'), $client->client_name]" />
    </x-slot:breadcrumbs>

    <flux:card class="my-6 relative">
        <div class="flex pr-8 gap-4">
            <flux:avatar :name="$client->client_name" color="auto" size="xl" class="xl:text-2xl size-14 xl:size-20" />
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-4 flex-wrap">
                    <flux:heading size="lg" class="truncate">{{ $client->client_name }}</flux:heading>
                    <flux:badge :color="$client->trashed() ? 'red' : $client->status->color()" rounded size="sm">&#9679; {{ $client->trashed() ? 'Deleted' : $client->status->label() }}</flux:badge>
                </div>
                <flux:text class="mt-2">{{ $client->company_name ?? 'Individual' }}</flux:text>
                <div class="flex flex-col md:flex-row flex-wrap gap-2 md:gap-x-5 mt-2">
                    <flux:link href="mailto:{{ $client->email }}" target="_blank" variant="subtle" class="font-normal">
                        <flux:icon name="envelope" variant="solid" class="size-4" />{{ $client->email }}
                    </flux:link>
                    <flux:separator vertical class="hidden md:block" />
                    <flux:link href="tel:{{ $client->phone }}" target="_blank" variant="subtle" class="font-normal">
                        <flux:icon name="phone" variant="solid" class="size-4" />{{ $client->phone }}
                    </flux:link>
                    <flux:separator vertical class="hidden md:block" />
                    <flux:text class="flex items-center gap-2 text-zinc-800 dark:text-zinc-200">
                        <flux:icon name="map-pin" variant="solid" class="size-4" />{{ $client->address }}
                    </flux:text>
                </div>
            </div>
        </div>
        <flux:separator class="my-5" />
        <div class="flex flex-wrap gap-3 ">
            <flux:badge icon="user">Created by {{ $client->createdBy?->name ?? 'System' }}</flux:badge>
            <flux:badge icon="calendar">Created on {{ $client->created_at->format('M j, Y') }}</flux:badge>
        </div>
        @canany(['update', 'delete', 'restore', 'forceDelete'], $client)
        <flux:dropdown align="end" class="absolute top-6 right-6">
            <flux:button size="sm" icon="ellipsis-vertical" variant="ghost" />
            <flux:menu>
                @if($client->trashed())
                @can('restore', $client)
                <flux:menu.item icon="arrow-uturn-left" wire:click="$dispatch('confirm-action', { id: '{{ $client->id }}', eventName: 'restore-client', title: 'Restore Client', description: 'Are you sure you want to restore {{ addslashes($client->client_name) }}?', actionText: 'Restore', actionVariant: 'primary' })">Restore</flux:menu.item>
                @endcan

                @can('forceDelete', $client)
                <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-action', { id: '{{ $client->id }}', eventName: 'force-delete-client', title: 'Delete Client Forever', description: 'Are you sure you want to permanently delete {{ addslashes($client->client_name) }}? This cannot be undone.', actionText: 'Delete Forever', actionVariant: 'danger' })">Delete Forever</flux:menu.item>
                @endcan
                @else
                @can('update', $client)
                <flux:menu.item icon="pencil-square" wire:click="$dispatch('edit-client', { id: '{{ $client->id }}' })">Edit</flux:menu.item>
                @endcan

                @can('delete', $client)
                <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-action', { id: '{{ $client->id }}', eventName: 'delete-client', title: 'Delete Client', description: 'Are you sure you want to delete {{ addslashes($client->client_name) }}?', actionText: 'Delete', actionVariant: 'danger' })">Delete</flux:menu.item>
                @endcan
                @endif
            </flux:menu>
        </flux:dropdown>
        @endcanany
    </flux:card>

    <x-tabs wire="currentTab" class="mt-6">
        <x-tabs.list>
            <x-tabs.tab name="projects" icon="building-office-2">Projects</x-tabs.tab>
            <x-tabs.tab name="tasks" icon="clipboard-document-list">Tasks</x-tabs.tab>
            <x-tabs.tab name="documents" icon="folder-open">Documents</x-tabs.tab>
        </x-tabs.list>

        <x-tabs.panel name="projects" class="pt-6">
            <x-projects.stats :stats="$this->projectStats" statusModel="projectStatusFilter" />
            <x-projects.table :projects="$this->projects" :showClient="false" :showActions="true" search-model="projectSearch" status-model="projectStatusFilter" sort-model="projectSortBy" :client-id="$client->id" />
        </x-tabs.panel>

        <x-tabs.panel name="tasks" class="pt-6">
            <x-tasks.stats :stats="$this->taskStats" statusModel="taskStatusFilter" />
            <x-tasks.table :tasks="$this->tasks" :statuses="$taskStatuses" :priorities="$taskPriorities" :showClient="false" search-model="taskSearch" status-model="taskStatusFilter" priority-model="taskPriorityFilter" sort-model="taskSortBy" :client-id="$client->id" />
        </x-tabs.panel>

        <x-tabs.panel name="documents" class="pt-6">
            <livewire:documents.index :client="$client" />
        </x-tabs.panel>
    </x-tabs>

    <livewire:clients.client-form />
    <livewire:projects.project-form />
    <livewire:tasks.task-form />
</div>