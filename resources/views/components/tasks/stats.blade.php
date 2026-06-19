@props([
    'stats',
    'statusModel' => 'status',
    'activeStatus' => null,
])

@php
$active = $activeStatus ?? $this->$statusModel;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5 mb-6">
    <x-stat-card icon="clipboard-document-list" color="auto" heading="Open Tasks" :value="$stats['total']" wire:click="setStatusFilter('open')" :active="$active === 'open'">
        @if($stats['overdue'] > 0)
        <flux:badge color="red" rounded size="sm">{{ $stats['overdue'] }} overdue</flux:badge>
        @endif
    </x-stat-card>
    <x-stat-card icon="clock" color="amber" heading="To Do" :value="$stats['todo']" wire:click="setStatusFilter('{{ \App\Enums\TaskStatus::Todo->value }}')" :active="$active === \App\Enums\TaskStatus::Todo->value" />
    <x-stat-card icon="play" color="blue" heading="In Progress" :value="$stats['in_progress']" wire:click="setStatusFilter('{{ \App\Enums\TaskStatus::InProgress->value }}')" :active="$active === \App\Enums\TaskStatus::InProgress->value" />
    <x-stat-card icon="pause" color="orange" heading="On Hold" :value="$stats['on_hold']" wire:click="setStatusFilter('{{ \App\Enums\TaskStatus::OnHold->value }}')" :active="$active === \App\Enums\TaskStatus::OnHold->value" />
    <x-stat-card icon="check" color="green" heading="Completed" :value="$stats['completed']" wire:click="setStatusFilter('{{ \App\Enums\TaskStatus::Completed->value }}')" :active="$active === \App\Enums\TaskStatus::Completed->value" />
    <x-stat-card icon="x-mark" color="zinc" heading="Cancelled" :value="$stats['cancelled']" wire:click="setStatusFilter('{{ \App\Enums\TaskStatus::Cancelled->value }}')" :active="$active === \App\Enums\TaskStatus::Cancelled->value" />
    <x-stat-card icon="trash" color="red" heading="Deleted Tasks" :value="$stats['deleted']" wire:click="setStatusFilter('{{ \App\Enums\ListFilter::Deleted->value }}')" :active="$active === \App\Enums\ListFilter::Deleted->value" />
</div>
