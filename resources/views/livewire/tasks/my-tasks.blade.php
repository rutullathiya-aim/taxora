<div>
    <x-slot:heading>My Tasks</x-slot:heading>

    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['My Tasks']" />
    </x-slot:breadcrumbs>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5 my-6">
        <x-stat-card icon="clipboard-document-list" color="auto" heading="Total Tasks" :value="$this->stats['total']">
            @if($this->stats['overdue'] > 0)
                <flux:badge color="red" rounded size="sm">{{ $this->stats['overdue'] }} overdue</flux:badge>
            @endif
        </x-stat-card>
        <x-stat-card icon="clock" color="amber" heading="To Do" :value="$this->stats['todo']" />
        <x-stat-card icon="play" color="blue" heading="In Progress" :value="$this->stats['in_progress']" />
        <x-stat-card icon="pause" color="orange" heading="On Hold" :value="$this->stats['on_hold']" />
    </div>
    <x-tasks.table :tasks="$this->tasks" :statuses="$statuses" :priorities="$priorities" />
    <livewire:tasks.task-form />
</div>
