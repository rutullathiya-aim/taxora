@props([
    'model',
    'documentId' => null,
])

<flux:modal wire:model="{{ $model }}" :closable="false" class="w-full max-w-[95vw] xl:max-w-7xl h-[90vh] !overflow-hidden">
    @if($documentId)
        @php
            $viewerDoc = \App\Models\ClientDocument::find($documentId);
            $ext = $viewerDoc ? strtolower(pathinfo($viewerDoc->name, PATHINFO_EXTENSION)) : '';
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
            $isPdf = $ext === 'pdf';
            $url = $viewerDoc ? route('documents.show', [$viewerDoc->id, $viewerDoc->name]) : '#';
        @endphp

        @if($viewerDoc)
            {{-- Modal Header --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4 overflow-hidden">
                    <flux:avatar icon="document-text" color="red"/>
                    <div class="min-w-0">
                        <flux:heading class="truncate" size="lg">{{ $viewerDoc->name }}</flux:heading>
                        <div class="flex items-center gap-2 mt-0.5">
                            <flux:badge size="sm" class="uppercase tracking-widest font-bold !text-[10px]">
                                {{ $ext ?: 'FILE' }}
                            </flux:badge>
                            <span class="text-xs text-zinc-500">&bull;</span>
                            <span class="text-xs text-zinc-500">{{ number_format($viewerDoc->size / 1024, 1) }} KB &bull; Uploaded {{ $viewerDoc->created_at->format('M d, Y g:i A') }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 ml-4">
                    <flux:button size="sm" icon="arrow-down-tray" variant="ghost" wire:click="downloadDocument('{{ $viewerDoc->id }}')"
                        title="Download" class="hidden sm:flex"/>
                    <flux:button size="sm" icon="arrow-top-right-on-square" variant="ghost" href="{{ $url }}"
                        target="_blank" title="Open in new tab" />
                    <flux:modal.close>
                        <flux:button size="sm" icon="x-mark" variant="ghost" title="Close"/>
                    </flux:modal.close>
                </div>
            </div>

            <flux:separator class="my-6"/>
            
            <div class="overflow-hidden relative flex items-center justify-center h-[calc(100%-10vh)]">
                @if($isImage)
                        <img src="{{ $url }}" alt="{{ $viewerDoc->name }}" class="max-h-full max-w-full object-contain">
                @elseif($isPdf)
                    <iframe src="{{ $url }}#toolbar=0&view=FitH" class="w-full h-full"></iframe>
                @else
                    <div class="flex flex-col items-center text-center w-150 gap-4">
                        <flux:avatar icon="document-text" color="red"/>
                        <flux:heading size="lg">Preview Not Available</flux:heading>
                        <flux:text class="text-zinc-500">
                            This file type (<strong>{{ strtoupper($ext) }}</strong>) cannot be previewed directly in the browser. Please download the file to view its contents.
                        </flux:text>
                        <flux:button wire:click="downloadDocument('{{ $viewerDoc->id }}')" icon="arrow-down-tray" variant="primary">
                            Download File to View
                        </flux:button>
                    </div>
                @endif
            </div>
        @endif
    @endif
</flux:modal>
