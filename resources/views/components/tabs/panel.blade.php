@props([
    'name',
])

@php
    $tabId = 'tab-' . $name . '-' . crc32($name);
    $panelId = 'tab-panel-' . $name . '-' . crc32($name);
@endphp

<div
    x-show="activeTab === '{{ $name }}'"
    x-cloak
    :data-selected="activeTab === '{{ $name }}' ? '' : undefined"
    :tabindex="activeTab === '{{ $name }}' ? '0' : '-1'"
    role="tabpanel"
    id="{{ $panelId }}"
    aria-labelledby="{{ $tabId }}"
    {{ $attributes }}
>
    {{ $slot }}
</div>
