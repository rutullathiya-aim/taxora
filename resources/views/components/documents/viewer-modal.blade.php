@props([
'model',
'documentId' => null,
])

<flux:modal wire:model="{{ $model }}" :closable="false" class="w-full max-w-[95vw] xl:max-w-7xl h-[90vh] !overflow-hidden">
    @if($documentId)
    @php
    $viewerDoc = $this->previewDocument;
    $url = $viewerDoc ? $viewerDoc->previewUrl() : '#';
    @endphp

    @if($viewerDoc)
    {{-- Modal Header --}}
    <div class="flex items-start justify-between">
        <div class="flex items-center gap-4 overflow-hidden">
            <flux:avatar icon="document-text" color="auto" />
            <div class="min-w-0">
                <flux:heading class="truncate" size="lg">{{ $viewerDoc->name }}</flux:heading>
                <div class="flex items-center gap-2 mt-0.5">
                    <flux:badge size="sm" class="uppercase tracking-widest font-bold !text-[10px]">{{ $viewerDoc->extension() ?: 'FILE' }}</flux:badge>
                    <flux:text size="sm">{{ $viewerDoc->humanSize() }} &bull; Uploaded by {{ $viewerDoc->createdBy->name ?? 'System' }} on {{ $viewerDoc->created_at->format('d M Y, h:i A') }}</flux:text>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0 ml-4">
            @if(method_exists($this, 'openRenameModal'))
            <flux:button size="sm" icon="pencil-square" variant="ghost" wire:click="openRenameModal('{{ $viewerDoc->id }}')" title="Rename" class="hidden sm:flex" />
            @endif
            <flux:button size="sm" icon="arrow-down-tray" variant="ghost" wire:click="downloadDocument('{{ $viewerDoc->id }}')" title="Download" class="hidden sm:flex" />
            <flux:button size="sm" icon="arrow-top-right-on-square" variant="ghost" href="{{ $url }}" target="_blank" title="Open in new tab" />
            <flux:modal.close>
                <flux:button size="sm" icon="x-mark" variant="ghost" title="Close" />
            </flux:modal.close>
        </div>
    </div>

    <flux:separator class="my-6" />

    <div class="overflow-hidden relative flex items-center justify-center h-[calc(100%-10vh)]">
        @if($viewerDoc->isImage())
        <img src="{{ $url }}" alt="{{ $viewerDoc->name }}" class="max-h-full max-w-full object-contain">
        @elseif($viewerDoc->isPdf())
        <iframe src="{{ $url }}#toolbar=0&view=FitH" class="w-full h-full"></iframe>
        @else
        <div class="flex flex-col items-center text-center w-150 gap-4">
            <flux:avatar icon="document-text" color="auto" />
            <flux:heading size="lg">Preview Not Available</flux:heading>
            <flux:text>This file type (<strong>{{ strtoupper($viewerDoc->extension()) }}</strong>) cannot be previewed directly in the browser. Please download the file to view its contents.</flux:text>
            <flux:button wire:click="downloadDocument('{{ $viewerDoc->id }}')" icon="arrow-down-tray" variant="primary">Download File</flux:button>
        </div>
        @endif
    </div>
    @endif
    @endif
</flux:modal>