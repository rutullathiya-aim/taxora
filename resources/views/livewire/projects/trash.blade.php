<div>
    <x-slot:heading>Trash</x-slot:heading>

    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Projects' => route('projects.index'), 'Trash']" />
    </x-slot:breadcrumbs>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5 my-6">
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="trash" color="red" circle />
                <div>
                    <flux:heading>Deleted Projects</flux:heading>
                    <flux:text class="font-medium">{{ $totalTrashed }}</flux:text>
                </div>
            </div>
        </flux:card>
    </div>

    <flux:card>
        <div class="flex flex-col md:flex-row md:items-center gap-4">
            <div class="w-full md:w-72">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search deleted projects..."
                    icon="magnifying-glass" />
            </div>

            <div class="w-full md:w-auto md:ml-auto flex items-center gap-2">
                <flux:button variant="subtle" href="{{ route('projects.index') }}" wire:navigate icon="arrow-left"
                    class="w-full md:w-auto">Back to Projects</flux:button>
            </div>
        </div>

        <flux:table class="mt-6">
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>Project Name</flux:table.column>
                <flux:table.column>Client</flux:table.column>
                <flux:table.column>Service</flux:table.column>
                <flux:table.column>Deleted At</flux:table.column>
                <flux:table.column align="center">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($projects as $index => $project)
                    <flux:table.row>
                        <flux:table.cell>
                            {{ ($projects->currentPage() - 1) * $projects->perPage() + $loop->iteration }}
                        </flux:table.cell>
                        <flux:table.cell class="font-medium flex items-center gap-2">
                            <flux:avatar circle size="sm" icon="folder" color="auto" />
                            {{ $project->project_name }}
                        </flux:table.cell>
                        <flux:table.cell>{{ $project->client->client_name ?? 'N/A' }}</flux:table.cell>
                        <flux:table.cell>{{ $project->service->name ?? 'N/A' }}</flux:table.cell>
                        <flux:table.cell>{{ $project->deleted_at->format('d M Y H:i') }}</flux:table.cell>

                        <flux:table.cell align="center">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" inset="top bottom" />
                                <flux:menu>
                                    <flux:menu.item icon="arrow-path" wire:click="restoreProject('{{ $project->id }}')">
                                        Restore
                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:modal.trigger :name="'delete-project-'.$project->id">
                                        <flux:menu.item icon="trash" variant="danger">
                                            Delete Permanently
                                        </flux:menu.item>
                                    </flux:modal.trigger>
                                </flux:menu>
                            </flux:dropdown>

                            <flux:modal :name="'delete-project-'.$project->id" class="min-w-[22rem]">
                                <form wire:submit="forceDeleteProject('{{ $project->id }}')">
                                    <div class="space-y-6">
                                        <div>
                                            <flux:heading size="lg">Delete Project Permanently?</flux:heading>
                                            <flux:text class="text-sm text-zinc-500 mt-2">
                                                Are you sure you want to permanently delete
                                                <b>{{ $project->project_name }}</b>? This action cannot be undone and all
                                                associated data will be lost.
                                            </flux:text>
                                        </div>

                                        <div class="flex gap-2 justify-end">
                                            <flux:modal.close>
                                                <flux:button variant="ghost">Cancel</flux:button>
                                            </flux:modal.close>

                                            <flux:button type="submit" variant="danger">Delete Permanently</flux:button>
                                        </div>
                                    </div>
                                </form>
                            </flux:modal>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-6 text-zinc-500">
                            No projects in trash.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if($projects->hasPages())
            <div class="mt-4">
                {{ $projects->links() }}
            </div>
        @endif
    </flux:card>
</div>
