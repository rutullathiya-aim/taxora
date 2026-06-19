<flux:modal wire:model="showModal" flyout class="!p-5 lg:!p-8 md:w-150">
    <form wire:submit.prevent="save" class="space-y-6">

        <flux:heading size="lg">{{ $task ? 'Edit Task' : 'Add Task' }}</flux:heading>
        <flux:separator />
        <flux:input :autofocus="! $task" wire:model="title" label="Task Title" placeholder="Enter task title" />
        <flux:textarea wire:model="description" label="Description" placeholder="Enter description" rows="4" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:select wire:model.live="client_id" label="Client" searchable>
                <flux:select.option value="">None</flux:select.option>
                @foreach($this->clients as $client)
                <flux:select.option value="{{ $client->id }}">{{ $client->company_name ?? $client->client_name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="project_id" label="Project" searchable>
                <flux:select.option value="">None</flux:select.option>
                @foreach($this->projects as $project)
                <flux:select.option value="{{ $project->id }}">{{ $project->project_name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:select wire:model="status" label="Status">
                @foreach($this->statuses as $statusOption)
                <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="priority" label="Priority">
                @foreach($this->priorities as $priorityOption)
                <flux:select.option value="{{ $priorityOption->value }}">{{ $priorityOption->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:field>
                <flux:label>Assign To</flux:label>
                <flux:dropdown align="start" class="w-full" x-data="{ buttonWidth: 0 }" x-init="buttonWidth = $el.offsetWidth; new ResizeObserver(() => buttonWidth = $el.offsetWidth).observe($el)">
                    <flux:button class="w-full !justify-between text-zinc-700 font-normal" icon-trailing="chevron-up-down">
                        @php($assigneeCount = count($assigned_to))
                        @if($assigneeCount)
                        {{ $assigneeCount }} selected
                        @else
                        Select assignees...
                        @endif
                    </flux:button>

                    <flux:menu class="max-h-[30rem] overflow-y-auto !p-2" x-bind:style="{ width: buttonWidth + 'px' }">
                        <div x-data="{ search: '' }">
                            <flux:input x-model="search" icon="magnifying-glass" placeholder="Search team members..." />
                            <flux:separator class="my-2" />
                            <flux:checkbox.group wire:model.live="assigned_to" class="space-y-3 max-h-50 [&_ui-label]:!font-normal [&_ui-label]:!text-zinc-700 dark:[&_ui-label]:!text-white">
                                @foreach($this->users as $user)
                                <div x-show="search === '' || '{{ strtolower($user->name) }}'.includes(search.toLowerCase())" @click.stop>
                                    <flux:checkbox value="{{ $user->id }}" label="{{ $user->name }} ({{ str($user->role->value)->title() }})" />
                                </div>
                                @endforeach
                            </flux:checkbox.group>
                        </div>
                    </flux:menu>
                </flux:dropdown>
            </flux:field>

            <flux:input type="datetime-local" wire:model="due_at" label="Due Date" />
        </div>

        <flux:separator />
        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">{{ $task ? 'Update Task' : 'Save Task' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </flux:button>
        </div>
    </form>
</flux:modal>