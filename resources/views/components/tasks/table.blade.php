@props([
'tasks',
'clientId' => null,
'projectId' => null,
'statuses',
'priorities',
'showClient' => true,
'showProject' => true,
'searchModel' => 'search',
'statusModel' => 'status',
'priorityModel' => 'priorityFilter',
'sortModel' => 'sortBy'
])

<flux:card>
    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
        <div class="w-full md:w-72">
            <flux:input wire:model.live.debounce.300ms="{{ $searchModel }}" placeholder="Search tasks..." icon="magnifying-glass" />
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="{{ $statusModel }}">
                <flux:select.option value="open">Open Tasks</flux:select.option>
                @foreach($statuses as $status)
                <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
                @endforeach
                <flux:select.option value="{{ \App\Enums\ListFilter::Deleted->value }}">{{ \App\Enums\ListFilter::Deleted->label() }}</flux:select.option>
            </flux:select>
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="{{ $priorityModel }}">
                <flux:select.option value="{{ \App\Enums\ListFilter::All->value }}">All Priorities</flux:select.option>
                @foreach($priorities as $priority)
                <flux:select.option value="{{ $priority->value }}">{{ $priority->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="{{ $sortModel }}">
                @foreach(\App\Enums\TaskSort::options() as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="w-full md:w-auto">
            <flux:button wire:click="resetFilters" icon="arrow-path" class="w-full md:w-auto text-zinc-700 dark:text-zinc-300 font-normal">Reset</flux:button>
        </div>

        <div class="w-full md:w-auto md:ml-auto">
            @can('create', App\Models\Task::class)
            <flux:button variant="primary" wire:click="$dispatch('create-task', { clientId: '{{ $clientId }}', projectId: '{{ $projectId }}' })" icon="plus" class="w-full md:w-auto">Add New Task</flux:button>
            @endcan
        </div>
    </div>

    <flux:table {{ $attributes }}>
        <flux:table.columns>
            <flux:table.column>#</flux:table.column>
            <flux:table.column>Task</flux:table.column>
            @if($showClient)
            <flux:table.column>Client</flux:table.column>
            @endif
            @if($showProject)
            <flux:table.column>Project</flux:table.column>
            @endif
            <flux:table.column>Assignee</flux:table.column>
            <flux:table.column>Priority</flux:table.column>
            <flux:table.column>Due Date</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column align="center">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($tasks as $task)
            <flux:table.row>
                <flux:table.cell>{{ \App\Support\TableSupport::rowNumber($tasks, $loop->index) }}</flux:table.cell>
                <flux:table.cell>
                    <flux:link href="{{ route('tasks.show', $task) }}" wire:navigate>{{ \Illuminate\Support\Str::limit($task->title, 40) }}</flux:link>
                    <flux:text size="sm">{{ $task->task_number }}</flux:text>
                </flux:table.cell>
                @if($showClient)
                <flux:table.cell>
                    @if($client = $task->client)
                    <flux:link href="{{ route('clients.show', $client) }}" wire:navigate class="flex items-center gap-2">
                        <flux:avatar circle size="sm" name="{{ $client->client_name }}" color="auto" />
                        {{ \Illuminate\Support\Str::limit($client->company_name ?? $client->client_name, 30) }}
                    </flux:link>
                    @else
                    <flux:text variant="strong">-</flux:text>
                    @endif
                </flux:table.cell>
                @endif
                @if($showProject)
                <flux:table.cell>
                    @if($project = $task->project)
                    <flux:link href="{{ route('projects.show', $project) }}" wire:navigate>{{ \Illuminate\Support\Str::limit($project->project_name, 30) }}</flux:link>
                    @else
                    <flux:text variant="strong">-</flux:text>
                    @endif
                </flux:table.cell>
                @endif
                <flux:table.cell>
                    @if($task->assignees->isNotEmpty())
                    <flux:avatar.group class="**:ring-zinc-100 dark:**:ring-zinc-800">
                        @foreach($task->assignees->take(3) as $assignee)
                        <flux:avatar size="sm" name="{{ $assignee->name }}" color="auto" circle title="{{ $assignee->name }}" />
                        @endforeach
                        @if($task->assignees->count() > 3)
                        <flux:avatar size="sm" color="zinc" circle title="{{ $task->assignees->skip(3)->pluck('name')->join(', ') }}">+{{ $task->assignees->count() - 3 }}</flux:avatar>
                        @endif
                    </flux:avatar.group>
                    @else
                    <flux:text variant="strong">Unassigned</flux:text>
                    @endif
                </flux:table.cell>
                <flux:table.cell>
                    <flux:badge :color="$task->priorityColor()" size="sm" inset="top bottom" rounded>
                        {{ $task->priorityLabel() }}
                    </flux:badge>
                </flux:table.cell>
                <flux:table.cell>
                    @if($task->due_at)
                    <div class="flex items-center gap-2 {{ $task->isOverdue ? 'text-red-600 dark:text-red-500 font-medium' : ''}}">
                        <flux:icon name="clock" variant="outline" class="size-4" />{{ $task->due_at->format('d M Y, h:i A') }}
                    </div>
                    @else
                    -
                    @endif
                </flux:table.cell>
                <flux:table.cell>
                    <flux:badge :color="$task->statusColor()" size="sm" inset="top bottom" rounded>&#9679; {{ $task->statusLabel() }}</flux:badge>
                </flux:table.cell>
                <flux:table.cell class="text-center">
                    <flux:dropdown align="end">
                        <flux:button size="sm" icon="ellipsis-vertical" variant="ghost" />

                        <flux:menu>
                            @can('view', $task)
                            <flux:menu.item icon="eye" href="{{ route('tasks.show', $task) }}" wire:navigate>View</flux:menu.item>
                            @endcan

                            @if($task->trashed())
                            @can('restore', $task)
                            <flux:menu.item icon="arrow-uturn-left" wire:click="$dispatch('confirm-action', { id: '{{ $task->id }}', eventName: 'restore-task', title: 'Restore Task', description: 'Are you sure you want to restore ' + {{ Js::from($task->title) }} + '?', actionText: 'Restore', actionVariant: 'primary' })">Restore</flux:menu.item>
                            @endcan
                            @can('forceDelete', $task)
                            <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-action', { id: '{{ $task->id }}', eventName: 'force-delete-task', title: 'Delete Task Forever', description: 'Are you sure you want to permanently delete ' + {{ Js::from($task->title) }} + '? This cannot be undone.', actionText: 'Delete Forever', actionVariant: 'danger' })">Delete Forever</flux:menu.item>
                            @endcan
                            @else
                            @can('update', $task)
                            <flux:menu.item icon="pencil-square" wire:click="$dispatch('edit-task', { id: '{{ $task->id }}' })">Edit</flux:menu.item>
                            @endcan

                            @can('delete', $task)
                            <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-action', { id: '{{ $task->id }}', eventName: 'delete-task', title: 'Delete Task', description: 'Are you sure you want to delete ' + {{ Js::from($task->title) }} + '?', actionText: 'Delete', actionVariant: 'danger' })">Delete</flux:menu.item>
                            @endcan
                            @endif
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="{{ 7 + ($showClient ? 1 : 0) + ($showProject ? 1 : 0) }}" class="text-center py-6">No tasks found</flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    @if($tasks->hasPages())
    <div class="mt-4">
        {{ $tasks->links() }}
    </div>
    @endif
</flux:card>