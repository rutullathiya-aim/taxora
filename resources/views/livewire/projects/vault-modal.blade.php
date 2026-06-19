<div>
    <flux:modal wire:model.live="showModal" :closable=false class="w-full max-w-[95vw] xl:max-w-7xl h-[90vh] !overflow-hidden">
        <div class="h-[80vh] flex flex-col">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Attach Documents</flux:heading>
                <div class="flex items-center gap-4">
                    <div class="w-full md:w-72">
                        <flux:input wire:model.live.debounce.300ms="vaultSearch" placeholder="Search vault..." icon="magnifying-glass" />
                    </div>
                    <div class="relative w-full md:w-72">
                        <flux:input type="file" wire:model="newDocuments" multiple class="!absolute w-full h-full opacity-0 cursor-pointer z-10" />
                        <flux:button icon="arrow-up-tray" variant="primary" class="w-full">
                            <span wire:loading.remove wire:target="newDocuments">Upload New Document(s)</span>
                            <span wire:loading wire:target="newDocuments">Uploading...</span>
                        </flux:button>
                    </div>
                </div>
            </div>
            <flux:separator class="my-5"/>
            <div class="flex-1 overflow-y-auto">
                <div class="sm:hidden mb-4">
                    <flux:input wire:model.live.debounce.300ms="vaultSearch" placeholder="Search vault..." icon="magnifying-glass" />
                </div>

                @if($Documents->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                        @foreach($Documents as $doc)
                            @php
                                $isAttached = in_array($doc->id, $alreadyAttachedDocumentIds);
                                $isSelected = in_array($doc->id, $selectedVaultDocumentIds);
                                $ext = strtolower(pathinfo($doc->name, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                                $isPdf = $ext === 'pdf';
                                $url = route('documents.show', [$doc->id, $doc->name]);
                            @endphp

                            <flux:card wire:key="vault-doc-{{ $doc->id }}" class="group relative !p-0 border transition overflow-hidden {{ ($isSelected || $isAttached) ? '!border-accent' : 'hover:border-accent' }}">
                                <div class="absolute top-3 left-3 z-20">
                                    <flux:checkbox wire:model.live="selectedVaultDocumentIds" value="{{ $doc->id }}" wire:click.stop />
                                </div>
                                <div class="absolute top-3 right-3 z-20">
                                    @if($isAttached)
                                        <flux:badge rounded color="zinc" size="sm">Attached</flux:badge>
                                    @endif
                                </div>
                                
                                <div class="h-48 w-full relative flex items-center justify-center border-b overflow-hidden {{ $doc->trashed() ? 'opacity-50 grayscale cursor-not-allowed' : 'cursor-pointer' }}" @if(!$doc->trashed()) wire:click="$dispatch('view-document', { documentId: '{{ $doc->id }}' })" @endif>
                                    @if($isImage)
                                        <img src="{{ $url }}" alt="{{ $doc->name }}" class="max-h-full max-w-full object-contain">
                                    @elseif($isPdf)
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-red-50/50 dark:bg-red-500/5 text-red-500">
                                            <div class="bg-red-100 dark:bg-red-500/20 p-4 rounded-2xl mb-3 shadow-sm">
                                                <flux:icon name="document-text" class="size-12 text-red-600 dark:text-red-400" variant="solid" />
                                            </div>
                                            <flux:text class="font-bold uppercase text-red-700/70 dark:text-red-400/80">PDF Document</flux:text>
                                        </div>
                                    @elseif(in_array($ext, ['xls', 'xlsx', 'csv']))
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-emerald-50/50 dark:bg-emerald-500/5 text-emerald-500">
                                            <div class="bg-emerald-100 dark:bg-emerald-500/20 p-4 rounded-2xl mb-3 shadow-sm">
                                                <flux:icon name="table-cells" class="size-12 text-emerald-600 dark:text-emerald-400" variant="solid" />
                                            </div>
                                            <flux:text class="font-bold uppercase text-emerald-700/70 dark:text-emerald-400/80">Spreadsheet</flux:text>
                                        </div>
                                    @elseif(in_array($ext, ['doc', 'docx']))
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-blue-50/50 dark:bg-blue-500/5 text-blue-500">
                                            <div class="bg-blue-100 dark:bg-blue-500/20 p-4 rounded-2xl mb-3 shadow-sm">
                                                <flux:icon name="document-text" class="size-12 text-blue-600 dark:text-blue-400" variant="solid" />
                                            </div>
                                            <flux:text class="font-bold uppercase text-blue-700/70 dark:text-blue-400/80">Word Document</flux:text>
                                        </div>
                                    @else
                                        <div
                                            class="w-full h-full flex flex-col items-center justify-center text-zinc-400 transition-transform duration-700 group-hover:scale-105">
                                            <div class="bg-zinc-100 dark:bg-zinc-800 p-4 rounded-2xl mb-3 shadow-sm">
                                                <flux:icon name="document" class="size-12" variant="solid" />
                                            </div>
                                            <span class="text-sm font-bold tracking-widest uppercase text-zinc-500">{{ $ext ?: 'File' }} Document</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="p-3 flex flex-col flex-1 cursor-pointer" wire:click="$dispatch('view-document', { documentId: '{{ $doc->id }}' })">
                                    <flux:heading class="truncate text-sm" title="{{ $doc->name }}">{{ $doc->name }}</flux:heading>
                                    <div class="flex items-center justify-between text-xs mt-1 text-zinc-500">
                                        <flux:text>{{ number_format($doc->size / 1024, 1) }} KB</flux:text>
                                        <flux:badge size="sm">{{ $doc->created_at->format('M d, Y') }}</flux:badge>
                                    </div>
                                </div>
                            </flux:card>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-full text-zinc-500 py-10">
                        <flux:icon name="folder-open" class="size-12 mb-3 text-zinc-300 dark:text-zinc-600" />
                        <flux:heading size="md" class="mb-1">No Document(s) Found</flux:heading>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <flux:separator class="my-5"/>
            <div class="flex justify-end">
                <div class="flex-1">
                    @if(count($selectedVaultDocumentIds) > 0)
                        <flux:heading class="mb-2">Selected Documents ({{ count($selectedVaultDocumentIds) }})</flux:heading>
                        <div class="flex flex-wrap gap-2 max-h-24 overflow-y-auto">
                            @foreach($selectedVaultDocumentIds as $selectedId)
                                @php
                                    $selectedDoc = $Documents->firstWhere('id', $selectedId);
                                @endphp
                                @if($selectedDoc)
                                    <flux:badge color="red" class="max-w-[200px] inline-flex">
                                        <span class="truncate">{{ $selectedDoc->name }}</span>
                                        <flux:badge.close wire:click="removeSelection('{{ $selectedId }}')" />
                                    </flux:badge>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="flex justify-end gap-3 mt-auto">
                    <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
                    <flux:button variant="primary" wire:click="confirmVaultSelection">
                        Attach {{ count($selectedVaultDocumentIds) }} Document(s)
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
