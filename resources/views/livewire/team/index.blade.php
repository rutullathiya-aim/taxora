<div>
    <x-slot:heading>Team Members</x-slot:heading>
    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Team']" />
    </x-slot:breadcrumbs>
    <x-team.stats :stats="$this->stats" :user="$this->user" />
    <x-team.table :users="$this->users" />
    <livewire:team.team-form />
</div>