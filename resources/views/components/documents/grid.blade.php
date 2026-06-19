@props(['documents'])

<flux:card>
    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
        <div class="w-full md:w-72">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search documents..."
                icon="magnifying-glass" />
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="status">
                <flux:select.option value="{{ \App\Enums\ListFilter::All->value }}">{{ \App\Enums\ListFilter::All->label() }} Documents</flux:select.option>
                <flux:select.option value="active">Active Documents</flux:select.option>
                <flux:select.option value="{{ \App\Enums\ListFilter::Deleted->value }}">{{ \App\Enums\ListFilter::Deleted->label() }} Documents</flux:select.option>
            </flux:select>
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="sortBy">
                @foreach(\App\Enums\DocumentSort::options() as $value => $label)
                <flux:select.option value="{{ $value }}">Sort: {{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="w-full md:w-auto">
            <flux:button wire:click="resetFilters" icon="arrow-path" class="w-full md:w-auto text-zinc-700 dark:text-zinc-300 font-normal">Reset</flux:button>
        </div>

        <div class="w-full md:w-auto md:ml-auto">
            <div class="relative group">
                <input type="file" wire:model.live="newDocuments" multiple class="absolute inset-0 w-full h-full z-10 opacity-0 cursor-pointer" title="" />
                <flux:button variant="primary" icon="arrow-up-tray">
                    <span wire:loading.remove wire:target="newDocuments">Upload Documents</span>
                    <span wire:loading wire:target="newDocuments">Uploading...</span>
                </flux:button>
            </div>
        </div>
    </div>

    <flux:separator class="my-6" />
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-6">
        @forelse($documents as $doc)
        <x-documents.preview-card :document="$doc" />
        @empty
        <div class="col-span-full">
            <flux:card class="flex flex-col items-center justify-center py-15">
                <div class="relative flex flex-col items-center text-center">
                    <flux:icon name="folder-open" class="size-15 mb-5 text-red-500" variant="solid" />
                    <flux:heading size="xl" class="mb-5">No Documents Found</flux:heading>

                    <div class="relative group">
                        <flux:input type="file" wire:model.live="newDocuments" multiple class="!absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                        <flux:button variant="primary" icon="arrow-up-tray">
                            <span wire:loading.remove wire:target="newDocuments">Upload Document</span>
                            <span wire:loading wire:target="newDocuments">Uploading to vault...</span>
                        </flux:button>
                    </div>
                </div>
            </flux:card>
        </div>
        @endforelse
    </div>
</flux:card>