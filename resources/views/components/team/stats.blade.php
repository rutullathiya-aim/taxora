@props([
'stats',
'user',
'statusModel' => 'status',
'activeStatus' => null,
])

@php
$active = $activeStatus ?? $this->$statusModel;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-5 my-6">
    <x-stat-card icon="users" color="blue" heading="Total Members" :value="$stats['total']" wire:click="setStatusFilter('{{ \App\Enums\UserListStatus::All->value }}')" :active="$active === \App\Enums\UserListStatus::All->value" class="cursor-pointer hover:inset-shadow-sm" />
    <x-stat-card icon="check" color="green" heading="Active Members" :value="$stats['active']" wire:click="setStatusFilter('{{ \App\Enums\UserListStatus::Active->value }}')" :active="$active === \App\Enums\UserListStatus::Active->value" class="cursor-pointer hover:inset-shadow-sm" />
    <x-stat-card icon="x-mark" color="zinc" heading="Inactive Members" :value="$stats['inactive']" wire:click="setStatusFilter('{{ \App\Enums\UserListStatus::Inactive->value }}')" :active="$active === \App\Enums\UserListStatus::Inactive->value" class="cursor-pointer hover:inset-shadow-sm" />
    <x-stat-card icon="clock" color="amber" heading="Pending Members" :value="$stats['pending']" wire:click="setStatusFilter('{{ \App\Enums\UserListStatus::Pending->value }}')" :active="$active === \App\Enums\UserListStatus::Pending->value" class="cursor-pointer hover:inset-shadow-sm" />
    @if ($user->isAdminOrManager())
    <x-stat-card icon="trash" color="red" heading="Deleted Members" :value="$stats['deleted']" wire:click="setStatusFilter('{{ \App\Enums\UserListStatus::Deleted->value }}')" :active="$active === \App\Enums\UserListStatus::Deleted->value" class="cursor-pointer hover:inset-shadow-sm" />
    @endif
</div>