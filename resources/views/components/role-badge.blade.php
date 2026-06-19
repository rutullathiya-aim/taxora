@props(['role'])

@php
$roleEnum = $role instanceof \App\Enums\UserRole ? $role : \App\Enums\UserRole::tryFrom(strtolower($role));
$color = $roleEnum ? $roleEnum->color() : 'zinc';
$label = $roleEnum ? $roleEnum->label() : ucfirst(is_string($role) ? $role : '');
@endphp

<flux:badge :color="$color" size="sm" inset="top bottom" rounded {{ $attributes }}>
    &#9679; {{ $label }}
</flux:badge>