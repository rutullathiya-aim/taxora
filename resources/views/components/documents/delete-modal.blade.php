@props([
'model',
'title' => 'Delete Document',
'description' => 'Are you sure you want to delete this document?',
'showCheckbox' => false,
'checkboxModel' => 'deleteDocumentGlobally',
'checkboxLabel' => 'Delete Document File',
'confirmAction' => 'deleteDocument',
])

<flux:modal wire:model="{{ $model }}" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $title }}</flux:heading>
            <flux:text class="mt-2">{{ $description }}</flux:text>
        </div>

        @if($showCheckbox)
        <flux:checkbox wire:model="{{ $checkboxModel }}" label="{{ $checkboxLabel }}" />
        @endif

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="{{ $confirmAction }}">
                {{ str_contains(strtolower($title), 'remove') ? 'Remove' : 'Delete' }}
            </flux:button>
        </div>
    </div>
</flux:modal>