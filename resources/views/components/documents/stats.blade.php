@props([
'stats',
'statusModel' => 'status',
'activeStatus' => null,
])

@php
$active = $activeStatus ?? $this->$statusModel;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5 mb-6">
    <x-stat-card icon="folder-open" color="blue" heading="Total Documents" :value="$stats['total']" wire:click="setStatusFilter('{{ \App\Enums\ListFilter::All->value }}')" :active="$active === \App\Enums\ListFilter::All->value" />
    <x-stat-card icon="document-text" color="green" heading="Active Documents" :value="$stats['active']" wire:click="setStatusFilter('active')" :active="$active === 'active'" />
    <x-stat-card icon="trash" color="red" heading="Deleted Documents" :value="$stats['deleted']" wire:click="setStatusFilter('{{ \App\Enums\ListFilter::Deleted->value }}')" :active="$active === \App\Enums\ListFilter::Deleted->value" />
</div>