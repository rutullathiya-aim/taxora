<div>
    <x-slot:heading>{{ $project->project_name }}</x-slot:heading>
    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Projects' => route('projects.index'), $project->project_name]" />
    </x-slot:breadcrumbs>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 my-6">
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="users" circle color="auto" />
                <div>
                    <flux:heading>Client</flux:heading>
                    <flux:text class="font-medium">{{ $project->client->client_name }}</flux:text>
                </div>
            </div>

        </flux:card>
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="clipboard-document-list" circle color="auto" />
                <div>
                    <flux:heading>Service</flux:heading>
                    <flux:text class="font-medium">{{ $project->service->name }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="arrow-path" circle color="auto" />
                <div>
                    <flux:heading>Status</flux:heading>
                    <flux:text class="font-medium">
                        {{ ucwords(str_replace('_', ' ', $project->status)) }}
                    </flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-4">
                    <flux:avatar icon="chart-pie" circle color="auto" />
                    <div>
                        <flux:heading>Progress</flux:heading>
                        @php
                            $total = $project->projectChecklists->count();
                            $completed = $project->projectChecklists->whereIn('status', ['Approved'])->count();
                            $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
                        @endphp
                        <flux:text class="font-medium">{{ $completed }}/{{ $total }}
                            ({{ $percentage }}%)</flux:text>
                    </div>
                </div>
                <flux:progress :value="$percentage"
                    :color="($project->status === 'completed' || $percentage == 100) ? 'green' : null" class="-mb-3" />
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <flux:avatar icon="calendar-days" circle color="auto" />
                <div>
                    <flux:heading>Due Date</flux:heading>
                    <flux:text class="font-medium">
                        {{ $project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('d M Y') : '-' }}
                    </flux:text>
                </div>
            </div>
        </flux:card>
    </div>

    <flux:card>
        <div class="flex flex-col md:flex-row md:items-center gap-4">
            <div class="w-full md:w-72">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search documents..."
                    icon="magnifying-glass" />
            </div>

            <div class="w-full md:w-72">
                <flux:select wire:model.live="statusFilter">
                    <flux:select.option value="all">Status: All</flux:select.option>
                    <flux:select.option value="Pending">Status: Pending</flux:select.option>
                    <flux:select.option value="Submitted">Status: Submitted</flux:select.option>
                    <flux:select.option value="Approved">Status: Approved</flux:select.option>
                    <flux:select.option value="Rejected">Status: Rejected</flux:select.option>
                    <flux:select.option value="Not Applicable">Status: Not Applicable</flux:select.option>
                </flux:select>
            </div>

            @if(count($selectedChecklists) > 0)
                <div class="w-full md:w-72">
                    <flux:select wire:change="bulkUpdateStatus($event.target.value)"
                        wire:confirm="Are you sure you want to update the status of the selected items?">
                        <option value="" selected disabled>Set Status ({{ count($selectedChecklists) }})</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </flux:select>
                </div>
            @endif

            <div class="w-full md:w-auto md:ml-auto">
                <flux:button variant="primary" wire:click="createClient" icon="plus" class="w-full md:w-auto">Add New
                    Document
                </flux:button>
            </div>
        </div>

        <flux:table class="mt-6">
            <flux:table.columns>
                <flux:table.column class="w-10">
                    <flux:checkbox wire:model.live="selectAll" />
                </flux:table.column>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>Checklist Item</flux:table.column>
                <flux:table.column>Mandatory</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Document</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows> @forelse($checklists as $index => $checklist)
                <flux:table.row class="group">
                    <flux:table.cell class="align-top">
                        <flux:checkbox wire:model.live="selectedChecklists" value="{{ $checklist->id }}" />
                    </flux:table.cell>
                    <flux:table.cell class="align-top">{{ $index + 1 }}</flux:table.cell>

                    <flux:table.cell class="align-top">
                        <div class="font-medium flex items-start gap-3">
                            <div>{!! nl2br(e($checklist->name)) !!}</div>
                            @if($checklist->remarks)
                                <flux:tooltip :content="$checklist->remarks" class="h-5">
                                    <flux:button icon="information-circle" size="sm" variant="ghost" inset
                                        class="opacity-1 group-hover:opacity-100 transition-opacity" />
                                </flux:tooltip>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="align-top">
                        @if($checklist->is_mandatory)
                            <flux:badge color="red" size="sm">Required</flux:badge>
                        @else
                        <flux:badge color="zinc" size="sm">Optional</flux:badge> @endif
                    </flux:table.cell>

                    <flux:table.cell class="align-top">
                        @php
                            $statusColor = match ($checklist->status) {
                                'Pending' => '!text-amber-600 !border-amber-200 !bg-amber-50/50 dark:!border-amber-500/20 dark:!text-amber-400 dark:!bg-amber-500/10',
                                'Submitted' => '!text-blue-600 !border-blue-200 !bg-blue-50/50 dark:!border-blue-500/20 dark:!text-blue-400 dark:!bg-blue-500/10',
                                'Approved' => '!text-emerald-600 !border-emerald-200 !bg-emerald-50/50 dark:!border-emerald-500/20 dark:!text-emerald-400 dark:!bg-emerald-500/10',
                                'Rejected' => '!text-rose-600 !border-rose-200 !bg-rose-50/50 dark:!border-rose-500/20 dark:!text-rose-400 dark:!bg-rose-500/10',
                                'Not Applicable' => '!text-zinc-600 !border-zinc-200 !bg-zinc-50/50 dark:!border-zinc-500/20 dark:!text-zinc-400 dark:!bg-zinc-500/10',
                                default => '',
                            };
                        @endphp
                        <flux:select wire:change="updateStatus('{{ $checklist->id }}', $event.target.value)" size="sm"
                            class="{{ $statusColor }}">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected($checklist->status === $status)>{{ $status }}</option>
                            @endforeach
                        </flux:select>
                    </flux:table.cell>

                    <flux:table.cell class="align-top">
                        @if($checklist->documents->count() > 0)
                            <div class="space-y-2">
                                @foreach($checklist->documents as $doc)
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1 overflow-hidden">
                                            <flux:icon name="document-text" size="xs" class="text-blue-900 size-4" />
                                            <flux:text class="truncate max-w-[120px] text-blue-900">
                                                {{ $doc->clientDocument->name }}
                                            </flux:text>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0">
                                            <flux:button size="xs" icon="eye"
                                                wire:click="viewDocument('{{ $doc->clientDocument->id }}')"
                                                title="View Document" class="w-10 p-4" />
                                            <flux:button size="xs" icon="arrow-down-tray"
                                                wire:click="downloadDocument('{{ $doc->id }}')" title="Download Document"
                                                class="w-10 p-4" />
                                            <flux:button size="xs" icon="trash" color="danger"
                                                wire:click="deleteDocument('{{ $doc->id }}')"
                                                wire:confirm="Remove this document?" title="Delete Document"
                                                class="w-10 p-4 !text-red-500" />
                                        </div>
                                    </div>
                                    @unless($loop->last)
                                        <flux:separator />
                                    @endunless
                                @endforeach
                            </div>
                        @else
                            <span class="text-sm text-zinc-400">No file</span>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="align-top">
                        <div class=" flex items-center gap-3">
                            <flux:button size="xs" icon="paper-clip" wire:click="openVaultModal('{{ $checklist->id }}')"
                                title="Attach Documents" class="w-10 p-4" />

                            <flux:button size="xs" icon="message-square-more" color="info"
                                wire:click="openRemarksModal('{{ $checklist->id }}')" title="Add Remarks"
                                class="w-10 p-4" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7">
                            <div class="text-center py-8 text-zinc-500">
                                No checklist items found for this project. Please ensure Checklist Templates exist for the
                                "{{ $project->service?->name }}" service.
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- Client Vault Browser Modal --}}
    <flux:modal wire:model.live="showVaultModal" class="min-w-[95vw] md:min-w-[85vw] lg:min-w-[75vw] pt-12">
        <div class="h-[80vh] flex flex-col">
            <div class="flex items-center justify-between pb-4">
                <div>
                    <flux:heading size="lg">Attach Documents</flux:heading>
                    <flux:text>Select a document to preview or upload a new one</flux:text>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-64 hidden sm:block">
                        <flux:input wire:model.live.debounce.300ms="vaultSearch" placeholder="Search vault..."
                            icon="magnifying-glass" />
                    </div>
                    <div class="relative">
                        <flux:input type="file" wire:model="newDocuments" multiple
                            class="!absolute w-full h-full opacity-0 cursor-pointer z-10" />
                        <flux:button icon="arrow-up-tray" variant="primary">
                            <span wire:loading.remove wire:target="newDocuments">Upload New Document(s)</span>
                            <span wire:loading wire:target="newDocuments">Uploading...</span>
                        </flux:button>
                    </div>
                </div>
            </div>
            <flux:separator />
            <div class="flex-1 flex overflow-hidden py-4 gap-6">
                <div class="w-full md:w-1/2 lg:w-3/5 flex flex-col h-full">
                    <div class="sm:hidden mb-4">
                        <flux:input wire:model.live.debounce.300ms="vaultSearch" placeholder="Search vault..."
                            icon="magnifying-glass" />
                    </div>
                    <div class="flex-1 overflow-y-auto pr-4 space-y-3">
                        @forelse($clientDocuments as $doc)
                            @php
                                $isAttached = in_array($doc->id, $alreadyAttachedDocumentIds);
                                $isSelected = in_array($doc->id, $selectedVaultDocumentIds);
                                $isPreview = $previewDocumentId === $doc->id;
                            @endphp
                            <div wire:click="setPreviewDocument('{{ $doc->id }}')"
                                class="flex items-start gap-3 p-3 rounded-md border transition-all cursor-pointer {{ $isPreview ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-500/10 dark:border-indigo-500' : 'border-zinc-200 dark:border-zinc-700 hover:border-indigo-300 dark:hover:border-indigo-600' }} {{ $isSelected ? 'ring-1 ring-indigo-500' : '' }}">
                                <div class="pt-1">
                                    <flux:checkbox wire:model.live="selectedVaultDocumentIds" value="{{ $doc->id }}"
                                        wire:click.stop />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <flux:heading>{{ $doc->name }}</flux:heading>
                                        @if($isAttached)
                                            <flux:badge rounded>Already Attached</flux:badge>
                                        @endif
                                    </div>
                                    <flux:text>
                                        <span>{{ pathinfo($doc->name, PATHINFO_EXTENSION) ?: 'FILE' }}</span>
                                        <span>&bull;</span>
                                        <span>{{ number_format($doc->size / 1024, 1) }} KB</span>
                                        <span>&bull;</span>
                                        <span>Uploaded {{ $doc->created_at->diffForHumans() }}</span>
                                    </flux:text>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 text-zinc-500">
                                @if($vaultSearch)
                                    No documents match your search.
                                @else
                                    This client's vault is currently empty.
                                @endif
                            </div>
                        @endforelse
                    </div>

                    {{-- Selection Summary --}}
                    @if(count($selectedVaultDocumentIds) > 0)
                        <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <div class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Selected Documents ({{ count($selectedVaultDocumentIds) }})
                            </div>
                            <div class="flex flex-wrap gap-2 max-h-24 overflow-y-auto">
                                @foreach($selectedVaultDocumentIds as $selectedId)
                                    @php
                                        $selectedDoc = $clientDocuments->firstWhere('id', $selectedId);
                                    @endphp
                                    @if($selectedDoc)
                                        <div
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 text-sm max-w-[200px]">
                                            <span class="truncate">{{ $selectedDoc->name }}</span>
                                            <button wire:click="removeSelection('{{ $selectedId }}')"
                                                class="hover:text-indigo-900 dark:hover:text-indigo-100 focus:outline-none">
                                                <flux:icon name="x-mark" class="size-3.5" />
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right: Preview Pane --}}
                <div
                    class="hidden md:flex flex-col w-1/2 lg:w-2/5 h-full border-l border-zinc-200 dark:border-zinc-700 pl-6">
                    @if($previewDocumentId)
                        @php
                            $previewDoc = $clientDocuments->firstWhere('id', $previewDocumentId);
                        @endphp
                        @if($previewDoc)
                            <div class="flex items-center justify-between mb-4">
                                <flux:heading size="md" class="truncate pr-4">{{ $previewDoc->name }}</flux:heading>
                                <flux:button size="sm" icon="arrows-pointing-out" variant="ghost"
                                    href="{{ route('documents.show', [$previewDoc->id, $previewDoc->name]) }}" target="_blank"
                                    title="Expand Preview" />
                            </div>

                            <div
                                class="flex-1 bg-zinc-100 dark:bg-zinc-800 rounded-xl overflow-hidden flex items-center justify-center relative border border-zinc-200 dark:border-zinc-700">
                                @php
                                    $ext = strtolower(pathinfo($previewDoc->name, PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                                    $isPdf = $ext === 'pdf';
                                    $url = route('documents.show', [$previewDoc->id, $previewDoc->name]);
                                @endphp

                                @if($isImage)
                                    <img src="{{ $url }}" alt="Preview" class="max-w-full max-h-full object-contain p-2">
                                @elseif($isPdf)
                                    <iframe src="{{ $url }}" class="w-full h-full border-0"></iframe>
                                @else
                                    <div class="text-center p-6">
                                        <flux:icon name="document" class="size-16 mx-auto text-zinc-400 mb-4" />
                                        <div class="font-medium mb-1">Preview not available</div>
                                        <div class="text-sm text-zinc-500 mb-4">This file type cannot be previewed.</div>
                                        <flux:button href="{{ $url }}" target="_blank" icon="arrow-down-tray">Download File
                                        </flux:button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="h-full flex flex-col items-center justify-center text-zinc-500 text-center p-6">
                            <flux:icon name="eye" class="size-12 text-zinc-300 dark:text-zinc-600 mb-4" />
                            <p>Select a document to preview it here.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-3 mt-auto">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="confirmVaultSelection">
                    Attach {{ count($selectedVaultDocumentIds) }} Document(s)
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Remarks Modal --}}
    <flux:modal wire:model="showRemarksModal" max-width="md">
        <div class="space-y-6">
            <flux:heading size="lg">Remarks</flux:heading>

            <flux:textarea wire:model="remarks" label="Add notes or remarks"
                placeholder="Enter any remarks for this checklist item..." rows="4" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" wire:click="saveRemarks" wire:loading.attr="disabled">Save
                    Remarks
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Document Viewer Modal --}}
    <x-documents.viewer-modal model="showViewerModal" :document-id="$viewerDocumentId" />
</div>