<div>
    <x-slot:heading>Tasks</x-slot:heading>

    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Tasks']" />
    </x-slot:breadcrumbs>

    <x-tasks.stats :stats="$this->stats" />
    <x-tasks.table :tasks="$this->tasks" :statuses="$statuses" :priorities="$priorities" />
    <livewire:tasks.task-form />
</div>