@props(['users'])

<flux:card>
    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
        <div class="w-full md:w-72">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search team members..." icon="magnifying-glass" />
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="status">
                @foreach (\App\Enums\UserListStatus::cases() as $option)
                @if ($option !== \App\Enums\UserListStatus::Deleted || auth()->user()->isAdminOrManager())
                <flux:select.option value="{{ $option->value }}">{{ $option->label() }}</flux:select.option>
                @endif
                @endforeach
            </flux:select>
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="role">
                @foreach (\App\Enums\UserListRole::cases() as $option)
                <flux:select.option value="{{ $option->value }}">{{ $option->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="w-full md:w-72">
            <flux:select wire:model.live="sortBy">
                @foreach(\App\Enums\UserSort::options() as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="w-full md:w-auto">
            <flux:select wire:model.live="perPage">
                @foreach (config('taxora.pagination.options', [10, 25, 50, 100]) as $option)
                <flux:select.option value="{{ $option }}">{{ $option }} per page</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="w-full md:w-auto">
            <flux:button wire:click="resetFilters" icon="arrow-path" class="w-full md:w-auto text-zinc-700 dark:text-zinc-300 font-normal">Reset</flux:button>
        </div>

        <div class="w-full md:w-auto md:ml-auto">
            @can('create', \App\Models\User::class)
            <flux:button variant="primary" wire:click="$dispatch('create-team-member')" icon="plus" class="w-full md:w-auto">Add Team Member</flux:button>
            @endcan
        </div>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>#</flux:table.column>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Phone</flux:table.column>
            <flux:table.column>Role</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Last Login</flux:table.column>
            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            <flux:table.column align="center">Actions</flux:table.column>
            @endif
        </flux:table.columns>

        <flux:table.rows>
            @forelse($users as $user)
            @php $isCurrentUser = $user->id === auth()->id(); @endphp
            <flux:table.row>
                <flux:table.cell>{{ \App\Support\TableSupport::rowNumber($users, $loop->index) }}</flux:table.cell>
                <flux:table.cell>
                    <flux:link href="{{ route('team.show', $user) }}" wire:navigate>
                        <flux:avatar circle size="sm" name="{{ $user->name }}" color="auto" />{{ $user->name }}
                        @if($isCurrentUser)
                        <flux:badge size="sm" color="zinc" inset="top bottom">You</flux:badge>
                        @endif
                    </flux:link>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:link href="mailto:{{ $user->email }}" target="_blank">
                        <flux:icon name="envelope" variant="outline" class="size-4" /> {{ $user->email }}
                    </flux:link>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:link href="tel:{{ $user->phone }}">
                        <flux:icon name="phone" variant="outline" class="size-4" /> {{ $user->phone }}
                    </flux:link>
                </flux:table.cell>
                <flux:table.cell><x-role-badge :role="$user->role" /></flux:table.cell>
                <flux:table.cell>
                    <flux:badge :color="$user->statusColor()" size="sm" inset="top bottom" rounded>&#9679; {{ $user->statusLabel() }}</flux:badge>
                </flux:table.cell>
                <flux:table.cell>
                    @if($user->last_login_at)
                    <div class="flex items-center gap-2">
                        <flux:icon name="clock" variant="outline" class="size-4" />{{ $user->last_login_at->isToday() || $user->last_login_at->isYesterday() ? $user->last_login_at->diffForHumans() : $user->last_login_at->format('d M Y') }}
                    </div>
                    @else
                    Never
                    @endif
                </flux:table.cell>
                @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                <flux:table.cell class="text-center">
                    @canany(['update', 'delete', 'restore', 'forceDelete'], $user)
                    <flux:dropdown align="end">
                        <flux:button size="sm" icon="ellipsis-vertical" />

                        <flux:menu>
                            @if($user->trashed())
                            @can('restore', $user)
                            <flux:menu.item icon="arrow-uturn-left" wire:click="$dispatch('confirm-action', { id: '{{ $user->id }}', eventName: 'restore-team-member', title: 'Restore Team Member', description: 'Are you sure you want to restore ' + {{ Js::from($user->name) }} + '?', actionText: 'Restore', actionVariant: 'primary' })">Restore</flux:menu.item>
                            @endcan
                            @can('forceDelete', $user)
                            <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-action', { id: '{{ $user->id }}', eventName: 'force-delete-team-member', title: 'Delete Team Member Forever', description: 'Are you sure you want to permanently delete ' + {{ Js::from($user->name) }} + '? This cannot be undone.', actionText: 'Delete Forever', actionVariant: 'danger' })">Delete Forever</flux:menu.item>
                            @endcan
                            @else
                            @can('update', $user)
                            <flux:menu.item icon="pencil-square" wire:click="$dispatch('edit-team-member', { id: '{{ $user->id }}' })">Edit</flux:menu.item>

                            @if($user->email_verified_at === null)
                            <flux:menu.item icon="envelope" wire:click="resendInvitation('{{ $user->id }}')">Resend Invitation</flux:menu.item>
                            @else
                            <flux:menu.item icon="key" wire:click="sendPasswordReset('{{ $user->id }}')">Send Password Reset</flux:menu.item>
                            @endif

                            @if(! $isCurrentUser)
                            @if($user->email_verified_at !== null)
                            <flux:menu.item icon="{{ $user->is_active ? 'lock-closed' : 'lock-open' }}" wire:click="toggleStatus('{{ $user->id }}')">{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}</flux:menu.item>
                            @endif
                            @endif
                            @endcan
                            @can('delete', $user)
                            @if(! $isCurrentUser)
                            <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-action', { id: '{{ $user->id }}', eventName: 'delete-team-member', title: 'Delete Team Member', description: 'Are you sure you want to delete ' + {{ Js::from($user->name) }} + '?', actionText: 'Delete', actionVariant: 'danger' })">Delete</flux:menu.item>
                            @endif
                            @endcan
                            @endif
                        </flux:menu>
                    </flux:dropdown>
                    @endcanany
                </flux:table.cell>
                @endif
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="{{ auth()->user()->isAdminOrManager() ? 8 : 7 }}" class="text-center py-6">No team members found</flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    @if($users->hasPages())
    <div class="mt-6">
        {{ $users->links() }}
    </div>
    @endif
</flux:card>