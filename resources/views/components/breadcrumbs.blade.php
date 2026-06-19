@props(['links' => []])

<flux:breadcrumbs {{ $attributes }}>
    <flux:breadcrumbs.item href="{{ route('dashboard') }}" wire:navigate>Dashboard</flux:breadcrumbs.item>

    @foreach($links as $label => $url)
    @if(is_string($label))
    <flux:breadcrumbs.item href="{{ $url }}" wire:navigate>{{ $label }}</flux:breadcrumbs.item>
    @else
    <flux:breadcrumbs.item>{{ $url }}</flux:breadcrumbs.item>
    @endif
    @endforeach
</flux:breadcrumbs>