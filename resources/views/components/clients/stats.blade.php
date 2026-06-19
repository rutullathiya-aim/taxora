@props([
'stats',
'user',
'statusModel' => 'status',
'activeStatus' => null,
])

@php
$active = $activeStatus ?? $this->$statusModel;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5 my-6">
    <x-stat-card icon="users" color="blue" heading="Total Clients" :value="$stats['total']" wire:click="setStatusFilter('{{ \App\Enums\ClientListStatus::All->value }}')" :active="$active === \App\Enums\ClientListStatus::All->value" class="cursor-pointer hover:inset-shadow-sm" />
    <x-stat-card icon="check" color="green" heading="Active Clients" :value="$stats['active']" wire:click="setStatusFilter('{{ \App\Enums\ClientListStatus::Active->value }}')" :active="$active === \App\Enums\ClientListStatus::Active->value" class="cursor-pointer hover:inset-shadow-sm" />
    <x-stat-card icon="x-mark" color="zinc" heading="Inactive Clients" :value="$stats['inactive']" wire:click="setStatusFilter('{{ \App\Enums\ClientListStatus::Inactive->value }}')" :active="$active === \App\Enums\ClientListStatus::Inactive->value" class="cursor-pointer hover:inset-shadow-sm" />
    @if ($user->isAdminOrManager())
    <x-stat-card icon="trash" color="red" heading="Deleted Clients" :value="$stats['deleted']" wire:click="setStatusFilter('{{ \App\Enums\ClientListStatus::Deleted->value }}')" :active="$active === \App\Enums\ClientListStatus::Deleted->value" class="cursor-pointer hover:inset-shadow-sm" />
    @endif
</div>