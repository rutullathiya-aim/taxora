@props([
    'default' => null,
    'wire' => null,
])

<div 
    x-data="{
        activeTab: @if($wire) @entangle($wire) @else '{{ $default }}' @endif,
        tabs: [],
        tabElements: [],
        registerTab(name, el) {
            if (!this.tabs.includes(name)) {
                this.tabs.push(name);
            }
            this.tabElements.push({ name, el });
        },
        switchTab(name) {
            this.activeTab = name;
        },
        focusTab(name) {
            const tab = this.tabElements.find(t => t.name === name);
            if (tab) tab.el.focus();
        },
        nextTab() {
            const idx = this.tabs.indexOf(this.activeTab);
            const next = this.tabs[(idx + 1) % this.tabs.length];
            this.switchTab(next);
            this.focusTab(next);
        },
        prevTab() {
            const idx = this.tabs.indexOf(this.activeTab);
            const prev = this.tabs[(idx - 1 + this.tabs.length) % this.tabs.length];
            this.switchTab(prev);
            this.focusTab(prev);
        },
    }"
    {{ $attributes->merge(['class' => 'block']) }}
>
    {{ $slot }}
</div>
