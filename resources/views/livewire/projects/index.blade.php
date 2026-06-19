<div>
    <x-slot:heading>Projects</x-slot:heading>
    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Projects']" />
    </x-slot:breadcrumbs>
    <x-projects.stats :stats="$this->stats" :user="$this->user" />
    <x-projects.table :projects="$this->projects" :user="$this->user" />
    <livewire:projects.project-form />
</div>