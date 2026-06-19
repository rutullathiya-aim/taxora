@props([
    'stats',
    'user',
    'statusModel' => 'status',
    'activeStatus' => null,
])

@php
$active = $activeStatus ?? $this->$statusModel;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-5 mb-6">
    <x-stat-card icon="folder" color="zinc" heading="All Projects" :value="$stats['total']" wire:click="setStatusFilter('{{ \App\Enums\ProjectListStatus::All->value }}')" :active="$active === \App\Enums\ProjectListStatus::All->value" class="cursor-pointer hover:inset-shadow-sm" />
    <x-stat-card icon="bolt" color="blue" heading="Active Projects" :value="$stats['active']" wire:click="setStatusFilter('{{ \App\Enums\ProjectListStatus::Active->value }}')" :active="$active === \App\Enums\ProjectListStatus::Active->value" class="cursor-pointer hover:inset-shadow-sm" />
    <x-stat-card icon="check-circle" color="green" heading="Completed Projects" :value="$stats['completed']" wire:click="setStatusFilter('{{ \App\Enums\ProjectListStatus::Completed->value }}')" :active="$active === \App\Enums\ProjectListStatus::Completed->value" class="cursor-pointer hover:inset-shadow-sm" />
    <x-stat-card icon="pause-circle" color="amber" heading="On Hold Projects" :value="$stats['on_hold']" wire:click="setStatusFilter('{{ \App\Enums\ProjectListStatus::OnHold->value }}')" :active="$active === \App\Enums\ProjectListStatus::OnHold->value" class="cursor-pointer hover:inset-shadow-sm" />
    <x-stat-card icon="x-circle" color="red" heading="Cancelled Projects" :value="$stats['cancelled']" wire:click="setStatusFilter('{{ \App\Enums\ProjectListStatus::Cancelled->value }}')" :active="$active === \App\Enums\ProjectListStatus::Cancelled->value" class="cursor-pointer hover:inset-shadow-sm" />
    @if ($user->isAdminOrManager())
    <x-stat-card icon="trash" color="rose" heading="Deleted Projects" :value="$stats['deleted']" wire:click="setStatusFilter('{{ \App\Enums\ProjectListStatus::Deleted->value }}')" :active="$active === \App\Enums\ProjectListStatus::Deleted->value" class="cursor-pointer hover:inset-shadow-sm" />
    @endif
</div>
