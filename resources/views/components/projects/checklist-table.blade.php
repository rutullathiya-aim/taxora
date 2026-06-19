@props(['checklists', 'projectId' => null])

<flux:card>
    <div class="flex flex-col md:flex-row md:items-center gap-4">
        <div class="w-full md:w-72">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search documents..." icon="magnifying-glass" />
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="status">
                <flux:select.option value="all">Status: All</flux:select.option>
                <flux:select.option value="Pending">Status: Pending</flux:select.option>
                <flux:select.option value="Submitted">Status: Submitted</flux:select.option>
                <flux:select.option value="Approved">Status: Approved</flux:select.option>
                <flux:select.option value="Rejected">Status: Rejected</flux:select.option>
                <flux:select.option value="Not Applicable">Status: Not Applicable</flux:select.option>
            </flux:select>
        </div>

        <div class="w-full md:w-auto md:ml-auto">
            <flux:button variant="primary" wire:click="$dispatch('open-add-checklist-modal', { projectId: '{{ $projectId }}' })" icon="plus" class="w-full md:w-auto">Add New Checklist Items</flux:button>
        </div>
    </div>

    <flux:table class="mt-6">
        <flux:table.columns>
            <flux:table.column>#</flux:table.column>
            <flux:table.column>Checklist Item</flux:table.column>
            <flux:table.column>Mandatory</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Document</flux:table.column>
            <flux:table.column>Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows> @forelse($checklists as $index => $checklist)
            <flux:table.row class="group">
                <flux:table.cell class="align-top">{{ $index + 1 }}</flux:table.cell>

                <flux:table.cell class="align-top">
                    <div class="font-medium flex items-start gap-3">
                        <div>{!! nl2br(e($checklist->name)) !!}</div>
                        @if($checklist->description)
                            <flux:tooltip :content="$checklist->description" class="h-5">
                                <flux:button icon="information-circle" size="sm" variant="ghost" inset class="opacity-1 group-hover:opacity-100 transition-opacity" />
                            </flux:tooltip>
                        @endif
                    </div>
                </flux:table.cell>

                <flux:table.cell class="align-top">
                    @if($checklist->is_mandatory)
                        <flux:badge color="red" size="sm">Required</flux:badge>
                    @else
                    <flux:badge color="zinc" size="sm">Optional</flux:badge>
                    @endif
                </flux:table.cell>

                <flux:table.cell class="align-top">
                    <flux:select wire:change="updateStatus('{{ $checklist->id }}', $event.target.value)" size="sm"
                        class="{{ $checklist->status->selectClasses() }}">
                        @foreach(\App\Enums\ChecklistStatus::cases() as $status)
                            @php
                                $hasActiveDocuments = $checklist->documents->contains(fn($doc) => $doc->Document && !$doc->Document->trashed());
                                $isDisabled = $checklist->is_mandatory && !$hasActiveDocuments && in_array($status, [\App\Enums\ChecklistStatus::Submitted, \App\Enums\ChecklistStatus::Approved]);
                            @endphp
                            <option value="{{ $status->value }}" @selected($checklist->status === $status) @disabled($isDisabled)>{{ $status->value }}</option>
                        @endforeach
                    </flux:select>
                </flux:table.cell>

                <flux:table.cell class="align-top">
                    @if($checklist->documents->count() > 0)
                        <div class="space-y-2">
                            @foreach($checklist->documents as $doc)
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1 overflow-hidden">
                                        <flux:icon name="document-text" size="xs" class="text-blue-900 size-4 {{ $doc->Document->trashed() ? 'opacity-50' : '' }}" />
                                        <flux:text class="truncate max-w-[120px] text-blue-900 {{ $doc->Document->trashed() ? 'line-through opacity-50' : '' }}">{{ $doc->Document->name }}</flux:text>
                                        @if($doc->Document->trashed())
                                            <flux:badge color="red" size="sm" class="ml-1 scale-75 origin-left">Deleted</flux:badge>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <flux:button size="xs" icon="eye" wire:click="viewDocument('{{ $doc->Document->id }}')" title="View Document" class="w-10 p-4" />
                                        <flux:button size="xs" icon="arrow-down-tray" wire:click="downloadDocument('{{ $doc->Document->id }}')" title="Download Document" class="w-10 p-4" />
                                        <flux:button size="xs" icon="trash" color="danger" wire:click="openRemoveDocumentModal('{{ $doc->id }}')" title="Remove Document" class="w-10 p-4 !text-red-500" />
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
                        <flux:button size="xs" icon="paper-clip" wire:click="$dispatch('open-vault-modal', { checklistId: '{{ $checklist->id }}' })" title="Attach Documents" class="w-10 p-4" />

                        <flux:button size="xs" icon="message-square-more" color="info" wire:click="$dispatch('open-remarks-modal', { checklistId: '{{ $checklist->id }}' })" title="Add Remarks" class="w-10 p-4" />
                        
                        @if($checklist->is_manual)
                            <flux:button size="xs" icon="trash" color="danger" wire:click="$dispatch('confirm-action', { id: '{{ $checklist->id }}', eventName: 'delete-checklist', title: 'Delete Checklist Item', description: 'Are you sure you want to delete this checklist item?', actionText: 'Delete', actionVariant: 'danger' })" title="Delete Item" class="w-10 p-4 !text-red-500" />
                        @endif
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @empty
                <flux:table.row>
                    <flux:table.cell colspan="7">
                        <div class="text-center py-8 text-zinc-500">
                            No checklist items found
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</flux:card>
