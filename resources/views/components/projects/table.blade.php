@props([
'projects',
'clientId' => null,
'showClient' => true,
'searchModel' => 'search',
'statusModel' => 'status',
'sortModel' => 'sortBy',
'createEvent' => 'create-project',
'maxVisibleAssignees' => 3
])

<flux:card>
    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
        <div class="w-full md:w-72">
            <flux:input wire:model.live.debounce.300ms="{{ $searchModel }}" placeholder="Search projects..." icon="magnifying-glass" />
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="{{ $statusModel }}">
                <flux:select.option value="{{ \App\Enums\ProjectListStatus::All->value }}">{{ \App\Enums\ProjectListStatus::All->label() }}</flux:select.option>
                @foreach(\App\Enums\ProjectStatus::options() as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
                <flux:select.option value="{{ \App\Enums\ProjectListStatus::Deleted->value }}">{{ \App\Enums\ProjectListStatus::Deleted->label() }}</flux:select.option>
            </flux:select>
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="{{ $sortModel }}">
                @foreach(\App\Enums\ProjectSort::options() as $value => $label)
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
            @can('create', App\Models\Project::class)
            <flux:button variant="primary" wire:click="$dispatch('{{ $createEvent }}', { clientId: '{{ $clientId }}' })" icon="plus" class="w-full md:w-auto">Add New Project</flux:button>
            @endcan
        </div>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>#</flux:table.column>
            <flux:table.column>Project</flux:table.column>
            @if($showClient)
            <flux:table.column>Client</flux:table.column>
            @endif
            <flux:table.column>Progress</flux:table.column>
            <flux:table.column>Assignees</flux:table.column>
            <flux:table.column>Due Date</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="center">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($projects as $project)
            <flux:table.row>
                <flux:table.cell>{{ \App\Support\TableSupport::rowNumber($projects, $loop->index) }}</flux:table.cell>
                <flux:table.cell>
                    <flux:link href="{{ route('projects.show', $project) }}" wire:navigate class="truncate max-w-[450px]">{{ $project->project_name }}</flux:link>
                    <flux:text size="sm">{{ $project->project_code }} - {{ $project->service?->name ?? 'No Service' }}</flux:text>
                </flux:table.cell>
                @if($showClient)
                <flux:table.cell>
                    @if($project->client)
                    <flux:link href="{{ route('clients.show', $project->client) }}" wire:navigate>
                        <flux:avatar circle size="sm" name="{{ $project->client->client_name }}" color="auto" />{{ $project->client->client_name }}
                    </flux:link>
                    @else
                    <flux:text>Client Deleted</flux:text>
                    @endif
                </flux:table.cell>
                @endif
                <flux:table.cell>
                    <div class="flex items-center gap-3 w-32">
                        <flux:progress value="{{ $project->progress }}" :color="($project->isCompleted() && $project->progress === 100) ? 'green' : null" />
                        <flux:text size="sm">{{ $project->progress }}%</flux:text>
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    @if($project->assignees->isNotEmpty())
                    <flux:avatar.group class="**:ring-zinc-100 dark:**:ring-zinc-800">
                        @foreach($project->assignees->take($maxVisibleAssignees) as $assignee)
                        <flux:avatar size="sm" name="{{ $assignee->name }}" color="auto" circle title="{{ $assignee->name }}" />
                        @endforeach
                        @if($project->assignees->count() > $maxVisibleAssignees)
                        <flux:avatar size="sm" color="zinc" circle title="{{ $project->assignees->skip($maxVisibleAssignees)->pluck('name')->join(', ') }}">+{{ $project->assignees->count() - $maxVisibleAssignees }}</flux:avatar>
                        @endif
                    </flux:avatar.group>
                    @else
                    <flux:text>Unassigned</flux:text>
                    @endif
                </flux:table.cell>
                <flux:table.cell>
                    <div class="flex items-center gap-2 {{ $project->isOverdue() ? 'text-red-600 dark:text-red-500 font-medium' : '' }}">
                        @if($project->due_date)
                        <flux:icon name="calendar-days" variant="outline" class="size-4" />{{ $project->due_date->isoFormat('DD MMM YYYY') }}
                        @else
                        -
                        @endif
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    @php
                    $displayStatus = \App\Enums\ProjectListStatus::fromState($project->status, $project->trashed());
                    @endphp
                    <flux:badge :color="$displayStatus->color()" size="sm" inset="top bottom" rounded>&#9679; {{ $displayStatus->label() }}</flux:badge>
                </flux:table.cell>
                <flux:table.cell class="text-center">
                    <flux:dropdown align="end">
                        <flux:button size="sm" icon="ellipsis-vertical" />
                        <flux:menu>
                            @can('view', $project)
                            <flux:menu.item icon="eye" href="{{ route('projects.show', $project) }}" wire:navigate>View</flux:menu.item>
                            @endcan
                            @if($project->trashed())
                            @can('restore', $project)
                            <flux:menu.item icon="arrow-uturn-left" wire:click="$dispatch('confirm-action', { id: '{{ $project->id }}', eventName: 'restore-project', title: 'Restore Project', description: 'Are you sure you want to restore ' + {{ Js::from($project->project_name) }} + '?', actionText: 'Restore', actionVariant: 'primary' })">Restore</flux:menu.item>
                            @endcan
                            @can('forceDelete', $project)
                            <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-action', { id: '{{ $project->id }}', eventName: 'force-delete-project', title: 'Delete Forever', description: 'Are you sure you want to permanently delete ' + {{ Js::from($project->project_name) }} + '? This cannot be undone.', actionText: 'Delete Forever', actionVariant: 'danger' })">Delete Forever</flux:menu.item>
                            @endcan
                            @else
                            @can('update', $project)
                            <flux:menu.item icon="pencil-square" wire:click="$dispatch('edit-project', { id: '{{ $project->id }}' })">Edit</flux:menu.item>
                            @endcan

                            @can('delete', $project)
                            <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-action', { id: '{{ $project->id }}', eventName: 'delete-project', title: 'Delete Project', description: 'Are you sure you want to delete ' + {{ Js::from($project->project_name) }} + '?', actionText: 'Delete', actionVariant: 'danger' })">Delete</flux:menu.item>
                            @endcan
                            @endif
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="{{ $showClient ? '8' : '7' }}" class="text-center py-6">No projects found.</flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    @if($projects->hasPages())
    <div class="mt-6">
        {{ $projects->links() }}
    </div>
    @endif
</flux:card>