@props([
    'projects', 
    'showClient' => true, 
    'showActions' => true,
    'searchModel' => 'search',
    'statusModel' => 'statusFilter',
    'sortModel' => 'sortBy',
    'clientId' => null,
])

<div>
    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
        <div class="w-full md:w-72">
            <flux:input wire:model.live.debounce.300ms="{{ $searchModel }}" placeholder="Search projects..."
                icon="magnifying-glass" />
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="{{ $statusModel }}">
                <flux:select.option value="all">Status: All</flux:select.option>
                <flux:select.option value="in_progress">Status: In Progress</flux:select.option>
                <flux:select.option value="on_hold">Status: On Hold</flux:select.option>
                <flux:select.option value="completed">Status: Completed</flux:select.option>
            </flux:select>
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="{{ $sortModel }}">
                <flux:select.option value="latest">Sort: Latest</flux:select.option>
                <flux:select.option value="oldest">Sort: Oldest</flux:select.option>
                <flux:select.option value="a_to_z">Sort: A to Z</flux:select.option>
                <flux:select.option value="z_to_a">Sort: Z to A</flux:select.option>
            </flux:select>
        </div>
        
        <div class="w-full md:w-auto md:ml-auto">
            @if($clientId)
                <flux:button variant="primary" wire:click="$dispatch('create-project', { clientId: '{{ $clientId }}' })" icon="plus" class="w-full md:w-auto">
                    Add New Project
                </flux:button>
            @else
                <flux:button variant="primary" wire:click="$dispatch('create-project')" icon="plus" class="w-full md:w-auto">
                    Add New Project
                </flux:button>
            @endif
        </div>
    </div>

    <flux:table {{ $attributes }}>
    <flux:table.columns>
        <flux:table.column>Project</flux:table.column>
        @if($showClient)
            <flux:table.column>Client</flux:table.column>
        @endif
        <flux:table.column>Progress</flux:table.column>
        <flux:table.column>Due Date</flux:table.column>
        <flux:table.column>Status</flux:table.column>
        @if($showActions)
            <flux:table.column align="center">Actions</flux:table.column>
        @endif
    </flux:table.columns>

    <flux:table.rows>
        @forelse($projects as $project)
            <flux:table.row>
                <flux:table.cell>
                    <div class="flex items-center gap-3">
                        <flux:avatar :icon="$project->service?->icon ?? 'folder'" />
                        <div>
                            <a href="{{ route('projects.show', $project) }}" wire:navigate
                                class="font-medium text-zinc-800 dark:text-white hover:underline">
                                {{ $project->project_name }}
                            </a>
                            <div class="text-xs text-zinc-500 mt-0.5">{{ $project->service?->name ?? 'Unknown' }}
                            </div>
                        </div>
                    </div>
                </flux:table.cell>
                @if($showClient)
                    <flux:table.cell>{{ $project->client->client_name }}</flux:table.cell>
                @endif
                <flux:table.cell>
                    <div class="flex items-center gap-3 w-32">
                        <flux:progress value="{{ $project->progress }}"
                            :color="($project->status === 'completed' && $project->progress == 100) ? 'green' : null" />
                        <span class="text-xs font-medium text-zinc-500 w-8 text-right">{{ $project->progress }}%</span>
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    {{ $project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('d M Y') : '-' }}
                </flux:table.cell>
                <flux:table.cell>
                    @php
                        $statusColors = [
                            'in_progress' => 'blue',
                            'on_hold' => 'orange',
                            'completed' => 'green',
                        ];
                        $color = $statusColors[$project->status] ?? 'zinc';
                        $label = ucwords(str_replace('_', ' ', $project->status));
                    @endphp
                    <flux:badge :color="$color" size="sm" inset="top bottom" rounded>
                        {{ $label }}
                    </flux:badge>
                </flux:table.cell>
                @if($showActions)
                    <flux:table.cell class="text-center">
                        <flux:dropdown align="end">
                            <flux:button size="sm" icon="ellipsis-vertical" variant="ghost" />

                            <flux:menu>
                                <flux:menu.item icon="eye" href="{{ route('projects.show', $project) }}" wire:navigate>
                                    View</flux:menu.item>
                                <flux:menu.item icon="pencil-square"
                                    wire:click="$dispatch('edit-project', { id: '{{ $project->id }}' })">Edit
                                </flux:menu.item>
                                <flux:menu.item variant="danger" icon="trash" wire:click="deleteProject('{{ $project->id }}')"
                                    wire:confirm="Are you sure you want to delete this project?">Delete
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                @endif
            </flux:table.row>
        @empty
            <flux:table.row>
                <flux:table.cell colspan="{{ $showClient ? '6' : '5' }}" class="text-center py-6">No projects found.
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
</div>