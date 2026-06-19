<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-slate-50 dark:bg-slate-900">
    <flux:sidebar sticky collapsible="mobile"
        class="border-r border-slate-800/10 bg-white dark:border-white/20 dark:bg-slate-950 !p-0 !gap-0">
        <flux:sidebar.toggle class="lg:hidden !absolute right-0" icon="x-mark" />

        @persist('logo')
        <div
            class="px-5 py-1.5 lg:h-18 [&_img]:h-15 [&_img]:w-[175px] lg:[&_img]:w-auto [&_img]:object-contain border-b border-slate-800/10 dark:border-white/20">
            <a href="{{ route('dashboard') }}" wire:navigate class="">
                <x-app-logo href="#"></x-app-logo>
            </a>
        </div>
        @endpersist
        <flux:navlist variant="outline" class="[&_svg]:stroke-2 p-5 sidebar-nav">
            <flux:navlist.item variant="default" icon="home" :href="route('dashboard')"
                :current="request()->routeIs('dashboard')" wire:navigate>Dashboard</flux:navlist.item>
            <flux:navlist.item variant="default" icon="users" :href="route('clients.index')"
                :current="request()->routeIs('clients.*')" wire:navigate>Clients</flux:navlist.item>
            <flux:navlist.item variant="default" icon="building-office-2" :href="route('projects.index')"
                :current="request()->routeIs('projects.*')" wire:navigate>Projects</flux:navlist.item>
            <flux:navlist.item variant="default" icon="shield-check" :href="route('services.index')"
                :current="request()->routeIs('services.*')" wire:navigate>Services</flux:navlist.item>
            <flux:navlist.item variant="default" icon="user-group" :href="route('team.index')"
                :current="request()->routeIs('team.*')" wire:navigate>Team</flux:navlist.item>
            @can('viewAny', App\Models\Task::class)
            <flux:navlist.item variant="default" icon="clipboard-document-list" :href="route('tasks.index')"
                :current="request()->routeIs('tasks.index') || request()->routeIs('tasks.show')" wire:navigate>Tasks</flux:navlist.item>
            @endcan
            @if(auth()->user()->hasRole('manager') || auth()->user()->hasRole('staff'))
            <flux:navlist.item variant="default" icon="clipboard-document-check" :href="route('tasks.my')"
                :current="request()->routeIs('tasks.my')" wire:navigate>My Tasks</flux:navlist.item>
            @endif
            <flux:navlist.item variant="default" icon="cog-6-tooth" :href="route('settings.profile')"
                :current="request()->routeIs('settings.*')" wire:navigate>Settings</flux:navlist.item>
        </flux:navlist>
    </flux:sidebar>

    <flux:header
        class="border-b border-slate-800/10 bg-white dark:border-white/20 dark:bg-slate-950 h-18 sticky top-0 !px-5">


        @persist('mobile-header-logo')
        <a href="{{ route('dashboard') }}"
            class="mr-5 flex items-center space-x-2 lg:hidden [&_img]:w-[175px] lg:[&_img]:w-auto [&_img]:object-contain"
            wire:navigate>
            <x-app-logo href="#"></x-app-logo>
        </a>
        @endpersist

        <div class="flex-1" x-data="{ heading: '{{ addslashes($heading ?? '') }}' }"
            @update-heading.window="heading = $event.detail">
            @if(isset($heading))
            <flux:heading size="xl" class="hidden lg:block" x-text="heading">{{ $heading }}</flux:heading>
            @endif
        </div>

        <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle"
            aria-label="Toggle dark mode" class="!text-zinc-300 dark:!text-white mr-2 !hidden lg:!inline-flex" />

        <flux:dropdown position="bottom" align="end">
            <flux:profile avatar:color="auto" circle :name="auth()->user()->name" :initials="auth()->user()->initials()"
                icon-trailing="chevron-down" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" x-show="! $flux.dark"
                        class="lg:hidden">Dark Mode</flux:menu.item>
                    <flux:menu.item x-data x-on:click="$flux.dark = ! $flux.dark" icon="sun" x-show="$flux.dark"
                        class="lg:hidden">Light Mode</flux:menu.item>
                    <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Settings</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
        <flux:sidebar.toggle class="lg:hidden !w-5 !ml-0" icon="bars-2" inset="left" />
    </flux:header>

    {{ $slot }}

    @persist('toast')
    <flux:toast position="bottom right" />
    @endpersist

    @fluxScripts
</body>

</html>