@props(['document'])

<flux:card class="group relative !p-0 border border-transparent hover:border-accent transition overflow-hidden">
    <div class="absolute top-3 left-3 z-20">
        @if($document->trashed())
        <flux:badge color="red" size="sm" inset="top bottom">Deleted</flux:badge>
        @endif
    </div>
    <div class="absolute top-3 right-3 z-20 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <flux:dropdown align="end">
            <flux:button size="sm" icon="ellipsis-vertical" variant="outline" />
            <flux:menu>
                @if($document->trashed())
                <flux:menu.item icon="arrow-uturn-left" x-on:click="$dispatch('confirm-action', { id: '{{ $document->id }}', eventName: 'restore-document', title: 'Restore Document', description: 'Are you sure you want to restore this document?', actionText: 'Restore', actionVariant: 'primary' })">Restore</flux:menu.item>
                <flux:menu.item variant="danger" icon="trash" x-on:click="$dispatch('confirm-action', { id: '{{ $document->id }}', eventName: 'force-delete-document', title: 'Delete Forever', description: 'Are you sure you want to permanently delete this document? This action cannot be undone.', actionText: 'Delete Forever', actionVariant: 'danger' })">Delete Forever</flux:menu.item>
                @else
                <flux:menu.item icon="eye" wire:click="viewDocument('{{ $document->id }}')">Preview</flux:menu.item>
                <flux:menu.item icon="pencil-square" wire:click="openRenameModal('{{ $document->id }}')">Rename</flux:menu.item>
                <flux:menu.item icon="arrow-down-tray" wire:click="downloadDocument('{{ $document->id }}')">Download</flux:menu.item>
                <flux:menu.item variant="danger" icon="trash" x-on:click="$dispatch('confirm-action', { id: '{{ $document->id }}', eventName: 'delete-document', title: 'Delete Document', description: 'Are you sure you want to delete this document?', actionText: 'Delete', actionVariant: 'danger' })">Delete</flux:menu.item>
                @endif
            </flux:menu>
        </flux:dropdown>
    </div>
    <div class="h-48 w-full relative flex items-center justify-center border-b overflow-hidden {{ $document->trashed() ? 'opacity-50 grayscale cursor-not-allowed' : 'cursor-pointer' }}" @if(!$document->trashed()) wire:click="viewDocument('{{ $document->id }}')" @endif>
        @if($document->isImage())
        <img src="{{ $document->previewUrl() }}" alt="{{ $document->name }}" class="max-h-full max-w-full object-contain">
        @elseif($document->isPdf())
        <div class="w-full h-full flex flex-col items-center justify-center bg-red-50/50 dark:bg-red-500/5 text-red-500">
            <div class="bg-red-100 dark:bg-red-500/20 p-4 rounded-2xl mb-3 shadow-sm">
                <flux:icon name="document-text" class="size-12 text-red-600 dark:text-red-400" variant="solid" />
            </div>
            <flux:text class="font-bold uppercase text-red-700/70 dark:text-red-400/80">PDF Document</flux:text>
        </div>
        @elseif($document->isSpreadsheet())
        <div class="w-full h-full flex flex-col items-center justify-center bg-emerald-50/50 dark:bg-emerald-500/5 text-emerald-500">
            <div class="bg-emerald-100 dark:bg-emerald-500/20 p-4 rounded-2xl mb-3 shadow-sm">
                <flux:icon name="table-cells" class="size-12 text-emerald-600 dark:text-emerald-400" variant="solid" />
            </div>
            <flux:text class="font-bold uppercase text-emerald-700/70 dark:text-emerald-400/80">Spreadsheet</flux:text>
        </div>
        @elseif($document->isWord())
        <div class="w-full h-full flex flex-col items-center justify-center bg-blue-50/50 dark:bg-blue-500/5 text-blue-500">
            <div class="bg-blue-100 dark:bg-blue-500/20 p-4 rounded-2xl mb-3 shadow-sm">
                <flux:icon name="document-text" class="size-12 text-blue-600 dark:text-blue-400" variant="solid" />
            </div>
            <flux:text class="font-bold uppercase text-blue-700/70 dark:text-blue-400/80">Word Document</flux:text>
        </div>
        @else
        <div class="w-full h-full flex flex-col items-center justify-center text-zinc-400 transition-transform duration-700 group-hover:scale-105">
            <div class="bg-zinc-100 dark:bg-zinc-800 p-4 rounded-2xl mb-3 shadow-sm">
                <flux:icon name="document" class="size-12" variant="solid" />
            </div>
            <span class="text-sm font-bold tracking-widest uppercase text-zinc-500">{{ strtoupper($document->extension()) ?: 'FILE' }} Document</span>
        </div>
        @endif

        <div class="absolute bottom-2 left-2 z-20">
            <flux:badge size="sm" color="zinc" class="uppercase font-bold tracking-wider !text-[10px]">{{ $document->extension() ?: 'FILE' }}</flux:badge>
        </div>
    </div>

    <div class="p-4 flex flex-col flex-1">
        <flux:heading class="truncate" title="{{ $document->name }}">{{ $document->name }}</flux:heading>

        <div class="mt-auto pt-2">
            <div class="flex items-center justify-between text-xs">
                <flux:text>{{ $document->created_at->format('M d, Y') }}</flux:text>
                <flux:badge size="sm" color="zinc">{{ $document->humanSize() }}</flux:badge>
            </div>
        </div>
    </div>
</flux:card>