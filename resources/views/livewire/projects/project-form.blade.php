<flux:modal wire:model="showModal" flyout class="!p-5 lg:!p-8">
    <form wire:submit.prevent="save" class="space-y-6">
        <div class="flex items-center gap-2">
            <flux:icon variant="outline" name="folder" class="size-4" />
            <flux:heading size="lg">{{ $project ? 'Edit Project' : 'Create Project' }}</flux:heading>
        </div>
        <flux:separator />

        <flux:select wire:model="client_id" label="Client" placeholder="Select client...">
            <flux:select.option value="">Select Client</flux:select.option>
            @foreach($this->clients as $client)
            <flux:select.option value="{{ $client->id }}">{{ $client->company_name ?: $client->client_name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input :autofocus="! $project" wire:model="project_name" label="Project Name" placeholder="Enter project name" />

        <flux:select wire:model="service_id" label="Service" :disabled="(bool)$project" placeholder="Select service...">
            <flux:select.option value="">Select Service</flux:select.option>
            @foreach($this->services as $service)
            <flux:select.option value="{{ $service->id }}">{{ $service->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input type="date" wire:model="due_date" label="Due Date" />

        @if ($project)
        <flux:select wire:model="status" label="Status">
            @foreach(\App\Enums\ProjectStatus::options() as $value => $label)
            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        @endif

        <flux:field>
            <flux:label>Assign To</flux:label>
            <flux:dropdown align="start" class="w-full" x-data="{ buttonWidth: 0 }" x-init="buttonWidth = $el.offsetWidth; new ResizeObserver(() => buttonWidth = $el.offsetWidth).observe($el)">
                <flux:button class="w-full !justify-between text-zinc-700 font-normal" icon-trailing="chevron-up-down">
                    @php($assigneeCount = count($assignees))
                    @if($assigneeCount)
                    {{ $assigneeCount }} selected
                    @else
                    Select users...
                    @endif
                </flux:button>

                <flux:menu class="max-h-[30rem] overflow-y-auto !p-2" x-bind:style="{ width: buttonWidth + 'px' }">
                    <div x-data="{ search: '' }">
                        <flux:input x-model="search" icon="magnifying-glass" placeholder="Search team members..." />
                        <flux:separator class="my-2" />
                        <flux:checkbox.group wire:model.live="assignees" class="space-y-3 max-h-50 [&_ui-label]:!font-normal [&_ui-label]:!text-zinc-700 dark:[&_ui-label]:!text-white">
                            @foreach($this->staffMembers as $staff)
                            <div x-show="search === '' || '{{ strtolower($staff->name) }}'.includes(search.toLowerCase())" @click.stop>
                                <flux:checkbox value="{{ $staff->id }}" label="{{ $staff->name }} ({{ str($staff->role->value)->title() }})" />
                            </div>
                            @endforeach
                        </flux:checkbox.group>
                    </div>
                </flux:menu>
            </flux:dropdown>
        </flux:field>

        <flux:separator />
        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>

            <flux:button type="submit" variant="primary" icon="bookmark-square" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">{{ $project ? 'Update Project' : 'Save Project' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </flux:button>
        </div>
    </form>
</flux:modal>
