@props([])

<div {{ $attributes->merge(['class' => 'tab', 'role' => 'tablist']) }}>
    {{ $slot }}
</div>
