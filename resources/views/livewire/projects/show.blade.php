<div>
    <x-slot:heading>{{ $project->project_name }}</x-slot:heading>
    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Projects' => route('projects.index'), $project->project_name]" />
    </x-slot:breadcrumbs>

    <flux:card class="flex flex-col md:flex-row md:justify-between gap-4 my-6">
        <div class="flex items-start gap-3 min-w-0">
            <flux:avatar icon="folder" color="auto" />
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-4 flex-wrap">
                    <flux:heading size="lg" class="truncate">{{ $project->project_name }}</flux:heading>
                    @php
                    $displayStatus = \App\Enums\ProjectListStatus::fromState($project->status, $project->trashed());
                    @endphp
                    <flux:badge :color="$displayStatus->color()" rounded size="sm">{{ $displayStatus->label() }}</flux:badge>
                </div>

                <flux:text class="mt-2 truncate">
                    <a href="{{ route('clients.show', $project->client) }}" wire:navigate>{{ $project->client->client_name }}</a>&mdash; {{ $project->service->name }}
                </flux:text>

                <div class="flex flex-wrap items-center gap-2 md:gap-5 mt-5 *:!text-zinc-500 dark:*:!text-white/70">
                    <div class="flex items-center gap-2 text-sm">
                        <flux:icon name="calendar-days" class="size-4" />Due: {{ $project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('d M Y') : 'No due date' }}
                    </div>

                    <flux:separator vertical class="hidden md:block" />

                    <div class="flex items-center gap-2 text-sm w-full md:w-auto">
                        <flux:icon name="chart-pie" class="size-4" />
                        <span class="whitespace-nowrap">Progress: {{ $this->progressStats['percentage'] }}% ({{ $this->progressStats['completed'] }}/{{ $this->progressStats['total'] }})</span>
                        <div class="w-24 ml-2 hidden md:block">
                            <flux:progress :value="$this->progressStats['percentage']" :color="$project->status === \App\Enums\ProjectStatus::Completed ? 'green' : null" />
                        </div>
                    </div>
                    @if($project->assignees->isNotEmpty())
                    <flux:separator vertical class="hidden md:block" />

                    <div class="flex items-center gap-2 text-sm">
                        @foreach($project->assignees as $assignee)
                        <flux:avatar size="sm" name="{{ $assignee->name }}" color="auto" circle title="{{ $assignee->name }}" />
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @canany(['update', 'delete', 'restore', 'forceDelete'], $project)
        <flux:dropdown align="end">
            <flux:button size="sm" icon="ellipsis-vertical" variant="ghost" />
            <flux:menu>
                @if($project->trashed())
                @can('restore', $project)
                <flux:menu.item icon="arrow-uturn-left" wire:click="$dispatch('confirm-action', { id: '{{ $project->id }}', eventName: 'restore-project', title: 'Restore Project', description: 'Are you sure you want to restore ' + {{ Js::from($project->project_name) }} + '?', actionText: 'Restore', actionVariant: 'primary' })">Restore</flux:menu.item>
                @endcan
                @can('forceDelete', $project)
                <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-action', { id: '{{ $project->id }}', eventName: 'force-delete-project', title: 'Delete Project Forever', description: 'Are you sure you want to permanently delete this project? This cannot be undone.', actionText: 'Delete Forever', actionVariant: 'danger' })">Delete Forever</flux:menu.item>
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
        @endcanany
    </flux:card>

    <x-tabs wire="currentTab" class="mt-6">
        <x-tabs.list>
            <x-tabs.tab name="checklists" icon="document-check">Checklists</x-tabs.tab>
            <x-tabs.tab name="tasks" icon="clipboard-document-list">Tasks</x-tabs.tab>
        </x-tabs.list>

        <x-tabs.panel name="checklists" class="pt-6">
            <x-projects.checklist-table :checklists="$this->checklists" :project-id="$project->id" />
        </x-tabs.panel>

        <x-tabs.panel name="tasks" class="pt-6">
            <x-tasks.table
                :tasks="$this->tasks"
                :statuses="$taskStatuses"
                :priorities="$taskPriorities"
                :showProject="false"
                search-model="taskSearch"
                status-model="taskStatusFilter"
                priority-model="taskPriorityFilter"
                sort-model="taskSortBy"
                :client-id="$project->client_id"
                :project-id="$project->id" />
        </x-tabs.panel>
    </x-tabs>

    <livewire:projects.vault-modal />
    <livewire:projects.remarks-modal />
    <livewire:projects.add-checklist-modal />

    <x-documents.viewer-modal model="showViewerModal" :document-id="$previewDocumentId" />
    <livewire:tasks.task-form />
    <livewire:projects.project-form />

    <x-documents.delete-modal
        model="showRemoveDocumentModal"
        title="Remove Document"
        description="Are you sure you want to remove this document from the checklist?"
        :showCheckbox="!$isDocumentTrashed"
        confirmAction="confirmRemoveDocument" />
</div>