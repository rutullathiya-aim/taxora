<div>
    <x-slot:heading>{{ $teamMember->name }}</x-slot:heading>
    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Team' => route('team.index'), $teamMember->name]" />
    </x-slot:breadcrumbs>

    <flux:card class="flex flex-col md:flex-row md:justify-between gap-4 my-6">
        <div class="flex items-start gap-3 min-w-0">
            <flux:avatar :name="$teamMember->name" color="auto" />
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-4 flex-wrap">
                    <flux:heading size="lg" class="truncate">{{ $teamMember->name }}</flux:heading>
                    
                    @if($teamMember->trashed())
                        <flux:badge color="red" rounded size="sm">Deleted</flux:badge>
                    @elseif($teamMember->status->value === 'active')
                        <flux:badge color="green" rounded size="sm">Active</flux:badge>
                    @else
                        <flux:badge color="zinc" rounded size="sm">Inactive</flux:badge>
                    @endif

                    <flux:badge color="blue" rounded size="sm">{{ $teamMember->role->label() }}</flux:badge>
                </div>
                
                <div class="flex flex-wrap items-center gap-2 md:gap-5 mt-5 *:!text-zinc-500 dark:*:!text-white/70 *:hover:!text-accent dark:*:hover:!text-white/90 *:hover:!bg-transparent *:transition-colors *:!h-auto">
                    <flux:button href="mailto:{{ $teamMember->email }}" icon="envelope" variant="ghost" size="sm" inset target="_blank">{{ $teamMember->email }}</flux:button>
                    @if($teamMember->phone)
                        <flux:separator vertical class="hidden md:block !h-4" />
                        <flux:button href="tel:{{ $teamMember->phone }}" icon="phone" variant="ghost" size="sm" inset target="_blank">{{ $teamMember->phone }}</flux:button>
                    @endif
                    <flux:separator vertical class="hidden md:block !h-4" />
                    <flux:button icon="clock" variant="ghost" size="sm" inset class="cursor-default">
                        Last login: {{ $teamMember->last_login_at ? $teamMember->last_login_at->diffForHumans() : 'Never' }}
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:card>

    <x-tabs wire="currentTab" class="mt-6">
        <x-tabs.list>
            <x-tabs.tab name="projects" icon="building-office-2">Assigned Projects</x-tabs.tab>
            <x-tabs.tab name="tasks" icon="clipboard-document-list">Assigned Tasks</x-tabs.tab>
        </x-tabs.list>

        <x-tabs.panel name="projects" class="pt-6">
            <x-projects.table :projects="$this->projects" :showClient="true" :showActions="true" search-model="projectSearch" status-model="projectStatusFilter" sort-model="projectSortBy" />
        </x-tabs.panel>

        <x-tabs.panel name="tasks" class="pt-6">
            <x-tasks.table :tasks="$this->tasks" :statuses="$taskStatuses" :priorities="$taskPriorities" :showClient="true" search-model="taskSearch" status-model="taskStatusFilter" priority-model="taskPriorityFilter" sort-model="taskSortBy" />
        </x-tabs.panel>
    </x-tabs>
</div>
