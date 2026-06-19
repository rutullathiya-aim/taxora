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
    <x-stat-card icon="briefcase" color="blue" heading="Total Services" :value="$stats['total']" wire:click="setStatusFilter('{{ \App\Enums\ServiceListStatus::All->value }}')" :active="$active === \App\Enums\ServiceListStatus::All->value" class="cursor-pointer hover:inset-shadow-sm" />
    <x-stat-card icon="check" color="green" heading="Active Services" :value="$stats['active']" wire:click="setStatusFilter('{{ \App\Enums\ServiceListStatus::Active->value }}')" :active="$active === \App\Enums\ServiceListStatus::Active->value" class="cursor-pointer hover:inset-shadow-sm" />
    <x-stat-card icon="x-mark" color="zinc" heading="Inactive Services" :value="$stats['inactive']" wire:click="setStatusFilter('{{ \App\Enums\ServiceListStatus::Inactive->value }}')" :active="$active === \App\Enums\ServiceListStatus::Inactive->value" class="cursor-pointer hover:inset-shadow-sm" />
    @if ($user->isAdminOrManager())
    <x-stat-card icon="trash" color="red" heading="Deleted Services" :value="$stats['deleted']" wire:click="setStatusFilter('{{ \App\Enums\ServiceListStatus::Deleted->value }}')" :active="$active === \App\Enums\ServiceListStatus::Deleted->value" class="cursor-pointer hover:inset-shadow-sm" />
    @endif
</div>