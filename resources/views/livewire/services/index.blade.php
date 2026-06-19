<div>
    <x-slot:heading>Services</x-slot:heading>
    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Services']" />
    </x-slot:breadcrumbs>
    <x-services.stats :stats="$this->stats" :user="$this->user" />
    <x-services.grid :services="$this->services" :user="$this->user" />
    <livewire:services.service-form />
</div>