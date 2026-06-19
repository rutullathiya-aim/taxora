<x-layouts.app.sidebar :heading="$heading ?? null">
    <flux:main class="!p-5">
        @if(isset($heading))
            <div class="lg:hidden mb-4" x-data="{ heading: '{{ addslashes($heading ?? '') }}' }"
                @update-heading.window="heading = $event.detail">
                <flux:heading size="xl" x-text="heading">{{ $heading }}</flux:heading>
            </div>
        @endif

        @if(isset($breadcrumbs))
            <div class="mb-4">
                {{ $breadcrumbs }}
            </div>
        @endif

        {{ $slot }}
        
        <livewire:global-confirm-modal />
    </flux:main>
</x-layouts.app.sidebar>