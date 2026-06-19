<div>
    <x-slot:heading>{{ $task->task_number }}</x-slot:heading>

    <x-slot:breadcrumbs>
        <x-breadcrumbs :links="['Tasks' => route('tasks.index'), $task->title]" />
    </x-slot:breadcrumbs>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 my-6">
        <div class="lg:col-span-2 space-y-6">
            <flux:card>
                <div class="flex items-start gap-4">
                    <flux:avatar icon="clipboard-document-check" color="auto" />
                    <div class="flex-1">
                        <flux:heading size="lg">{{ $task->title }}</flux:heading>
                        @if($task->description)
                            <flux:text>{!! nl2br(e($task->description)) !!}</flux:text>
                        @else
                            <flux:text>No description provided.</flux:text>
                        @endif
                    </div>
                </div>

                <flux:separator class="my-6"/>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <flux:text>Related Client</flux:text>
                        @if($task->client)
                            <a href="{{ route('clients.show', $task->client) }}" wire:navigate class="flex items-start gap-2 mt-2">
                                <flux:icon name="building-office" class="size-5"/>
                                {{ $task->client->company_name ?? $task->client->client_name }}
                            </a>
                        @else
                            <flux:text>-</flux:text>
                        @endif
                    </div>
                    <div>
                        <flux:text>Related Project</flux:text>
                        @if($task->project)
                            <a href="{{ route('projects.show', $task->project_id) }}" wire:navigate class="flex items-start gap-2 mt-2">
                                <flux:icon name="folder" class="size-5" />
                                {{ $task->project->project_name }}
                            </a>
                        @else
                            <flux:text>-</flux:text>
                        @endif
                    </div>
                </div>
            </flux:card>

            <!-- Comments Section -->
            <flux:card>
                <div class="flex items-center justify-between mb-6">
                    <flux:heading size="lg">Comments & Updates</flux:heading>
                    <flux:badge size="sm">{{ $task->comments->count() }}</flux:badge>
                </div>

                @can('addComment', $task)
                    <form wire:submit.prevent="addComment">
                        <flux:textarea wire:model="newComment" placeholder="Add an update or comment..." rows="3" class="mb-6" />
                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary" icon="paper-airplane">Post Comment</flux:button>
                        </div>
                    </form>
                @endcan

                <flux:separator class="my-6"/>
                <div class="space-y-5">
                    @forelse($task->comments->sortByDesc('created_at') as $comment)
                        <flux:card class="!p-4">
                            <div class="flex gap-3 items-start">
                                <flux:avatar size="sm" name="{{ $comment->user->name }}" circle color="auto" />
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-3">
                                            <flux:heading size="base">{{ $comment->user->name }}</flux:heading>
                                            <flux:text size="xs">
                                                <x-date-format :date="$comment->created_at" />
                                            </flux:text>
                                        </div>
                                        @if(auth()->id() === $comment->user_id || auth()->user()->hasRole('admin'))
                                            <flux:button size="xs" variant="ghost" icon="trash" wire:click="$dispatch('confirm-action', { id: '{{ $comment->id }}', eventName: 'delete-comment', title: 'Delete Comment', description: 'Are you sure you want to delete this comment?', actionText: 'Delete', actionVariant: 'danger' })" class="hover:text-accent transition" />
                                        @endif
                                    </div>
                                    <flux:text>{{ $comment->comment }}</flux:text>
                                </div>
                            </div>
                        </flux:card>
                    @empty
                        <flux:text class="text-center">No comments yet.</flux:text>
                    @endforelse
                </div>
            </flux:card>
        </div>

        <div class="space-y-6">
            <flux:card class="space-y-5 [&_[data-flux-heading]]:mb-1">
                    <div>
                        <flux:heading>Status</flux:heading>
                        <flux:select wire:model.live="status" wire:change="updateStatus($event.target.value)" class="{{ $task->status->selectClasses() }}">
                            @foreach($statuses as $s)
                                <flux:select.option value="{{ $s->value }}">{{ $s->label() }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div>
                        <flux:heading>Priority</flux:heading>
                        <flux:badge :color="$task->priority->color()" size="sm">{{ $task->priority->label() }}</flux:badge>
                    </div>

                    <div>
                        <flux:heading>Assignees</flux:heading>
                        @if($task->assignees->isNotEmpty())
                            <div class="flex flex-wrap gap-4 mt-2">
                                @foreach($task->assignees as $assignee)
                                    <div class="flex items-center gap-2">
                                        <flux:avatar size="sm" circle name="{{ $assignee->name }}" color="auto" />
                                        <flux:text>{{ $assignee->name }}</flux:text>
                                    </div>
                                    @if (! $loop->last)
                                        <flux:separator vertical />
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <flux:text class="mt-2">Unassigned</flux:text>
                        @endif
                    </div>

                    <div>
                        <flux:heading>Due Date</flux:heading>
                        @if($task->due_at)
                            <div class="flex items-center gap-2">
                                <flux:icon.clock class="size-5" />
                                <flux:text>{{ $task->due_at->format('d M Y, h:i A') }}</flux:text>
                                @if($task->isOverdue)
                                    <flux:badge color="red" size="sm">Overdue</flux:badge>
                                @endif
                            </div>
                        @else
                            <flux:text>-</flux:text>
                        @endif
                    </div>

                    <flux:separator class="my-4"/>
                    <div class="flex items-center justify-between">
                        <flux:text>Created by {{ $task->createdBy->name }}</flux:text>
                        <flux:text>{{ $task->created_at->format('d M Y') }}</flux:text>
                    </div>
            </flux:card>

            <flux:card>
                <flux:heading class="mb-5">Activity Timeline</flux:heading>
                
                <div class="relative pl-6 border-l-1 space-y-6">
                    @forelse($this->timeline as $item)
                        <div class="relative">
                            <div class="absolute -left-[40px] bg-white dark:bg-zinc-900 rounded-full p-1.5 border-1">
                                @if($item['model']->type === 'comment_added')
                                    <flux:icon.chat-bubble-left-ellipsis class="size-4" />
                                @elseif($item['model']->type === 'created')
                                    <flux:icon.plus class="size-4" />
                                @elseif($item['model']->type === 'assigned' || $item['model']->type === 'reassigned')
                                    <flux:icon.user class="size-4" />
                                @elseif($item['model']->type === 'status_changed')
                                    <flux:icon.arrows-right-left class="size-4" />
                                @elseif($item['model']->type === 'completed')
                                    <flux:icon.check class="size-4" />
                                @else
                                    <flux:icon.pencil class="size-4" />
                                @endif
                            </div>
                            
                            <div class="flex gap-1">
                                <flux:heading>{{ $item['model']->user->name }}</flux:heading>
                                <flux:text>{{ strtolower($item['model']->description) }}</flux:text>
                            </div>
                            <flux:text class="text-xs"><x-date-format :date="$item['date']" /></flux:text>
                        </div>
                    @empty
                        <div class="text-sm text-zinc-500">No activity recorded.</div>
                    @endforelse
                </div>
            </flux:card>
        </div>
    </div>

    <!-- Edit Modal -->
    <livewire:tasks.task-form />
</div>
