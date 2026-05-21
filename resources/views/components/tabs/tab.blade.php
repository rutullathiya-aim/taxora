@props([
    'name',
    'icon' => null,
])

@php
    $tabId = 'tab-' . $name . '-' . crc32($name);
    $panelId = 'tab-panel-' . $name . '-' . crc32($name);
@endphp

<button
    type="button"
    x-init="registerTab('{{ $name }}', $el)"
    x-on:click="switchTab('{{ $name }}')"
    x-on:keydown.arrow-right.prevent="nextTab()"
    x-on:keydown.arrow-left.prevent="prevTab()"
    x-on:keydown.home.prevent="switchTab(tabs[0]); focusTab(tabs[0])"
    x-on:keydown.end.prevent="switchTab(tabs[tabs.length - 1]); focusTab(tabs[tabs.length - 1])"
    :data-selected="activeTab === '{{ $name }}' ? '' : undefined"
    :data-active="activeTab === '{{ $name }}' ? '' : undefined"
    :aria-selected="(activeTab === '{{ $name }}').toString()"
    :tabindex="activeTab === '{{ $name }}' ? '0' : '-1'"
    role="tab"
    id="{{ $tabId }}"
    aria-controls="{{ $panelId }}"
    {{ $attributes }}
>
    @if($icon)
        <flux:icon :name="$icon" class="size-5 shrink-0" />
    @endif
    {{ $slot }}
</button>
