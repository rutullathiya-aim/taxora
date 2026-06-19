<div>
    <x-slot:heading>Clients</x-slot:heading>
    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Clients']" />
    </x-slot:breadcrumbs>
    <x-clients.stats :stats="$this->stats" :user="$this->user" />
    <x-clients.table :clients="$this->clients" :user="$this->user" />
    <livewire:clients.client-form />
</div>