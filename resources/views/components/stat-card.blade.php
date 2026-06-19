@props(['icon', 'color' => 'blue', 'heading', 'value' => null, 'active' => false])

@php
$isClickable = $attributes->has('wire:click');
$cardClasses = $active
? 'inset-shadow-sm bg-zinc-50 dark:bg-zinc-900'
: ($isClickable ? 'hover:inset-shadow-sm hover:bg-zinc-50 dark:hover:bg-zinc-900 cursor-pointer' : '');
@endphp

<flux:card {{ $attributes->class($cardClasses)->merge([
    'role' => $isClickable ? 'button' : null,
    'tabindex' => $isClickable ? '0' : null,
    'aria-pressed' => $isClickable ? ($active ? 'true' : 'false') : null,
    '@keydown.enter' => $isClickable ? '$el.click()' : null,
    '@keydown.space.prevent' => $isClickable ? '$el.click()' : null,
]) }}>
    <div class="flex flex-col gap-3">
        <div class="flex items-center gap-4">
            <flux:avatar :icon="$icon" :color="$color" circle />
            <div>
                <flux:heading>{{ $heading }}</flux:heading>
                <div class="flex items-center gap-2">
                    @isset($value)
                    <flux:text class="font-medium">{{ $value }}</flux:text>
                    @endisset
                    {{ $slot }}
                </div>
            </div>
        </div>
        @if(isset($footer))
        {{ $footer }}
        @endif
    </div>
</flux:card>