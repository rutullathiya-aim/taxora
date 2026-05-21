<div>
    <x-slot:heading>{{ $client->client_name }}</x-slot:heading>
    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Clients' => route('clients.index'), $client->client_name]" />
    </x-slot:breadcrumbs>

    {{-- Client Details Header --}}
    <flux:card class="flex flex-col md:flex-row md:justify-between gap-4 my-6">
        <div class="flex items-start gap-3 min-w-0">
            <flux:avatar :name="$client->client_name" color="auto" />
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-4 flex-wrap">
                    <flux:heading size="lg" class="truncate">{{ $client->client_name }}</flux:heading>
                    @if($client->is_active)
                        <flux:badge color="green" rounded size="sm">Active</flux:badge>
                    @else
                        <flux:badge color="zinc" rounded size="sm">Inactive</flux:badge>
                    @endif
                </div>
                <flux:text class="mt-2 truncate">{{ $client->company_name ?? 'Individual' }}</flux:text>

                <div class="flex flex-wrap gap-2 md:gap-5 mt-5">
                    <flux:button href="mailto:{{ $client->email }}" icon="envelope" variant="ghost" size="sm"
                        class="!p-0 !text-zinc-500 dark:!text-white/70 hover:!text-accent dark:hover:!text-white/90 hover:!bg-transparent transition-colors !h-auto">
                        {{ $client->email }}
                    </flux:button>
                    <flux:separator vertical class="hidden md:block" />
                    <flux:button href="tel:{{ $client->phone }}" icon="phone" variant="ghost" size="sm"
                        class="!p-0 !text-zinc-500 dark:!text-white/70 hover:!text-accent dark:hover:!text-white/90 hover:!bg-transparent transition-colors !h-auto">
                        {{ $client->phone }}
                    </flux:button>
                    <flux:separator vertical class="hidden md:block" />
                    <flux:button icon="map-pin" variant="ghost" size="sm"
                        class="!p-0 !text-zinc-500 dark:!text-white/70 cursor-default hover:!bg-transparent !h-auto">
                        {{ $client->address }}
                    </flux:button>
                </div>
            </div>
        </div>
        <flux:dropdown align="end">
            <flux:button size="sm" icon="ellipsis-vertical" variant="ghost" />

            <flux:menu>
                <flux:menu.item icon="pencil-square" wire:click="$dispatch('edit-client', { id: '{{ $client->id }}' })">
                    Edit
                </flux:menu.item>
                <flux:menu.item variant="danger" icon="trash" wire:click="deleteClient('{{ $client->id }}')"
                    wire:confirm="Are you sure you want to delete this client?">Delete
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:card>

    <x-tabs wire="currentTab" class="mt-6">
        <x-tabs.list>
            <x-tabs.tab name="projects" icon="building-office-2">Projects</x-tabs.tab>
            <x-tabs.tab name="documents" icon="folder-open">Documents</x-tabs.tab>
        </x-tabs.list>

        <x-tabs.panel name="projects" class="pt-6">
            <flux:card>
                <x-projects.table :projects="$projects" :showClient="false" :showActions="true"
                    search-model="projectSearch" status-model="projectStatusFilter" sort-model="projectSortBy"
                    :client-id="$client->id" />
            </flux:card>
        </x-tabs.panel>

        <x-tabs.panel name="documents" class="pt-6">
            <flux:card>
                <x-documents.grid :documents="$documents" />
            </flux:card>
        </x-tabs.panel>
    </x-tabs>

    {{-- Premium Document Viewer Modal --}}
    <x-documents.viewer-modal model="showPreviewModal" :document-id="$previewDocumentId" />

    <flux:modal wire:model="showDeleteModal" class="max-w-md p-0 overflow-hidden">
        @if($deleteDocumentId)
            @php
                $delDoc = \App\Models\ClientDocument::find($deleteDocumentId);
            @endphp
            @if($delDoc)
                <div class="p-6 text-center">
                    <div
                        class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-500/20 mb-6">
                        <flux:icon name="exclamation-triangle" class="h-8 w-8 text-red-600 dark:text-red-400" variant="solid" />
                    </div>

                    <flux:heading size="xl" class="mb-2">Delete Document?</flux:heading>

                    <div
                        class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-4 mb-6 border border-zinc-100 dark:border-zinc-700/50 text-left">
                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100 line-clamp-2"
                            title="{{ $delDoc->name }}">
                            {{ $delDoc->name }}
                        </div>
                        <div class="text-xs text-zinc-500 mt-1">Uploaded {{ $delDoc->created_at->format('M d, Y') }}
                            &bull;
                            {{ number_format($delDoc->size / 1024, 1) }} KB
                        </div>
                    </div>

                    <flux:text class="text-zinc-500 dark:text-zinc-400 mb-8">
                        This action cannot be undone. The file will be permanently removed from the client vault.
                    </flux:text>

                    <div class="flex gap-3 w-full">
                        <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)" class="flex-1">Cancel
                        </flux:button>
                        <flux:button variant="danger" wire:click="deleteDocument" class="flex-1">Delete File
                        </flux:button>
                    </div>
                </div>
            @endif
        @endif
    </flux:modal>

    <livewire:clients.client-form />
    <livewire:projects.project-form />
</div>