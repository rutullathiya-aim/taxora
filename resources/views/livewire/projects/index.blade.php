<div>
    <x-slot:heading>Projects</x-slot:heading>

    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Projects']" />
    </x-slot:breadcrumbs>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5 my-6">
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="folder" color="blue" circle />
                <div>
                    <flux:heading>Total Projects</flux:heading>
                    <flux:text class="font-medium">{{ $totalProjects }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="play" color="blue" circle />
                <div>
                    <flux:heading>In Progress Projects</flux:heading>
                    <flux:text class="font-medium">{{ $inProgressProjects }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="check" color="green" circle />
                <div>
                    <flux:heading>Completed Projects</flux:heading>
                    <flux:text class="font-medium">{{ $completedProjects }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="pause" color="orange" circle />
                <div>
                    <flux:heading>On Hold Projects</flux:heading>
                    <flux:text class="font-medium">{{ $onHoldProjects }}</flux:text>
                </div>
            </div>
        </flux:card>
    </div>

    <flux:card class="mt-6">
        <x-projects.table :projects="$projects" />
    </flux:card>

    <livewire:projects.project-form />
</div>