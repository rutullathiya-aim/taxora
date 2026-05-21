@props([
    'documents',
    'searchModel' => 'documentSearch',
    'sortModel' => 'documentSortBy',
])

<div>
    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
        <div class="w-full md:w-72">
            <flux:input wire:model.live.debounce.300ms="{{ $searchModel }}" placeholder="Search documents..."
                icon="magnifying-glass" />
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="{{ $sortModel }}">
                <flux:select.option value="latest">Sort: Latest</flux:select.option>
                <flux:select.option value="oldest">Sort: Oldest</flux:select.option>
                <flux:select.option value="a_to_z">Sort: A to Z</flux:select.option>
                <flux:select.option value="z_to_a">Sort: Z to A</flux:select.option>
                <flux:select.option value="largest">Sort: Largest</flux:select.option>
                <flux:select.option value="smallest">Sort: Smallest</flux:select.option>
            </flux:select>
        </div>

        <div class="w-full md:w-auto md:ml-auto">
            <div class="relative group">
                <input type="file" wire:model.live="newDocuments" multiple class="absolute inset-0 w-full h-full z-10 opacity-0 cursor-pointer" title=""/>
                <flux:button variant="primary" icon="arrow-up-tray">
                    <span wire:loading.remove wire:target="newDocuments">Upload Documents</span>
                    <span wire:loading wire:target="newDocuments">Uploading...</span>
                </flux:button>
            </div>
        </div>
    </div>

    <flux:separator class="my-6"/>
    @if($documents->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-6">
            @foreach($documents as $doc)
                @php
                    $ext = strtolower(pathinfo($doc->name, PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                    $isPdf = $ext === 'pdf';
                    $url = route('documents.show', [$doc->id, $doc->name]);
                @endphp

                <flux:card
                    class="group relative !p-0 border border-transparent hover:border-accent transition overflow-hidden">
                    <div
                        class="absolute top-3 right-3 z-20 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <flux:button size="sm" icon="eye"
                            wire:click="viewDocument('{{ $doc->id }}')" title="Preview" />
                        <flux:button size="sm" icon="arrow-down-tray"
                            wire:click="downloadDocument('{{ $doc->id }}')" title="Download" />
                        <flux:button size="sm" icon="trash"
                            wire:click="confirmDelete('{{ $doc->id }}')" title="Delete" />
                    </div>

                    {{-- Thumbnail Area --}}
                    <div class="h-48 w-full relative bg-zinc-50 dark:bg-zinc-800/30 flex items-center justify-center border-b border-zinc-100 dark:border-zinc-800 overflow-hidden cursor-pointer"
                        wire:click="viewDocument('{{ $doc->id }}')">
                        <div
                            class="absolute inset-0 bg-gradient-to-b from-black/5 to-transparent z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                        </div>

                        @if($isImage)
                            <img src="{{ $url }}" alt="{{ $doc->name }}"
                                class="max-h-full max-w-full object-contain">
                        @elseif($isPdf)
                            <div
                                class="w-full h-full flex flex-col items-center justify-center bg-red-50/50 dark:bg-red-500/5 text-red-500 transition-transform duration-700 group-hover:scale-105">
                                <div class="bg-red-100 dark:bg-red-500/20 p-4 rounded-2xl mb-3 shadow-sm">
                                    <flux:icon name="document-text" class="size-12 text-red-600 dark:text-red-400"
                                        variant="solid" />
                                </div>
                                <span
                                    class="text-sm font-bold tracking-widest uppercase text-red-700/70 dark:text-red-400/80">PDF
                                    Document</span>
                            </div>
                        @elseif(in_array($ext, ['xls', 'xlsx', 'csv']))
                            <div
                                class="w-full h-full flex flex-col items-center justify-center bg-emerald-50/50 dark:bg-emerald-500/5 text-emerald-500 transition-transform duration-700 group-hover:scale-105">
                                <div class="bg-emerald-100 dark:bg-emerald-500/20 p-4 rounded-2xl mb-3 shadow-sm">
                                    <flux:icon name="table-cells" class="size-12 text-emerald-600 dark:text-emerald-400"
                                        variant="solid" />
                                </div>
                                <span
                                    class="text-sm font-bold tracking-widest uppercase text-emerald-700/70 dark:text-emerald-400/80">Spreadsheet</span>
                            </div>
                        @elseif(in_array($ext, ['doc', 'docx']))
                            <div
                                class="w-full h-full flex flex-col items-center justify-center bg-blue-50/50 dark:bg-blue-500/5 text-blue-500 transition-transform duration-700 group-hover:scale-105">
                                <div class="bg-blue-100 dark:bg-blue-500/20 p-4 rounded-2xl mb-3 shadow-sm">
                                    <flux:icon name="document-text" class="size-12 text-blue-600 dark:text-blue-400"
                                        variant="solid" />
                                </div>
                                <span
                                    class="text-sm font-bold tracking-widest uppercase text-blue-700/70 dark:text-blue-400/80">Word
                                    Document</span>
                            </div>
                        @else
                            <div
                                class="w-full h-full flex flex-col items-center justify-center text-zinc-400 transition-transform duration-700 group-hover:scale-105">
                                <div class="bg-zinc-100 dark:bg-zinc-800 p-4 rounded-2xl mb-3 shadow-sm">
                                    <flux:icon name="document" class="size-12" variant="solid" />
                                </div>
                                <span class="text-sm font-bold tracking-widest uppercase text-zinc-500">{{ $ext ?: 'File' }}
                                    Document</span>
                            </div>
                        @endif
                    </div>

                    {{-- Card Details --}}
                    <div class="p-4 flex flex-col flex-1">
                        <flux:heading class="truncate">{{ $doc->name }}</flux:heading>
                        <div class="flex items-center justify-between text-xs mt-2">
                            <flux:text>{{ $doc->created_at->format('M d, Y') }}</flux:text>
                            <flux:badge size="sm">{{ number_format($doc->size / 1024, 1) }} KB</flux:badge>
                        </div>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @else
        <div
            class="flex flex-col items-center justify-center py-20 px-4 bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm relative overflow-hidden">
            <div
                class="absolute inset-0 bg-grid-zinc-100/50 dark:bg-grid-zinc-800/20 [mask-image:radial-gradient(ellipse_at_center,black,transparent_70%)]">
            </div>

            <div class="relative flex flex-col items-center text-center z-10">
                <div class="relative mb-8">
                    <div class="absolute inset-0 bg-indigo-200 dark:bg-indigo-500/20 blur-3xl rounded-full opacity-50">
                    </div>
                    <div
                        class="w-24 h-24 bg-white dark:bg-zinc-800 rounded-3xl shadow-xl flex items-center justify-center rotate-3 border border-zinc-100 dark:border-zinc-700 relative z-10">
                        <flux:icon name="folder-open" class="size-12 text-indigo-500" variant="solid" />
                    </div>
                    <div
                        class="w-16 h-16 bg-white dark:bg-zinc-800 rounded-2xl shadow-lg flex items-center justify-center -rotate-6 absolute -bottom-4 -left-6 border border-zinc-100 dark:border-zinc-700 z-0 opacity-80">
                        <flux:icon name="document-text" class="size-8 text-blue-400" variant="solid" />
                    </div>
                    <div
                        class="w-16 h-16 bg-white dark:bg-zinc-800 rounded-2xl shadow-lg flex items-center justify-center rotate-12 absolute -top-4 -right-6 border border-zinc-100 dark:border-zinc-700 z-0 opacity-80">
                        <flux:icon name="photo" class="size-8 text-emerald-400" variant="solid" />
                    </div>
                </div>

                <flux:heading size="xl" class="mb-2 tracking-tight">Your Client Vault is Empty</flux:heading>
                <flux:text class="text-zinc-500 max-w-md mb-8">Securely store and share KYC documents, identity proofs,
                    and tax filings in this beautiful file explorer.</flux:text>

                <div class="relative group">
                    <flux:input type="file" wire:model.live="newDocuments" multiple
                        class="!absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                    <flux:button variant="primary" icon="arrow-up-tray"
                        class="shadow-md shadow-indigo-500/20 relative group-hover:shadow-lg group-hover:shadow-indigo-500/30 transition-all group-hover:-translate-y-0.5">
                        <span wire:loading.remove wire:target="newDocuments">Upload Your First Document</span>
                        <span wire:loading wire:target="newDocuments">Uploading to vault...</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
