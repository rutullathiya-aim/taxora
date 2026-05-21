@props(['users'])

<flux:card class="mt-6">
    <div class="flex flex-col md:flex-row md:items-center gap-4">
        <div class="w-full md:w-72">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search team members..."
                icon="magnifying-glass" />
        </div>
        <div class="w-full md:w-48">
            <flux:select wire:model.live="statusFilter">
                <flux:select.option value="active">Active Members</flux:select.option>
                <flux:select.option value="deleted">Deleted Members</flux:select.option>
            </flux:select>
        </div>
        <div class="w-full md:w-48">
            <flux:select wire:model.live="roleFilter">
                <flux:select.option value="">All Roles</flux:select.option>
                @foreach(\App\Enums\UserRole::cases() as $roleOption)
                    <flux:select.option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="w-full md:w-auto md:ml-auto">
            @can('create', App\Models\User::class)
                <flux:button variant="primary" wire:click="create" icon="plus" class="w-full md:w-auto">
                    Add Team Member
                </flux:button>
            @endcan
        </div>
    </div>

    <flux:table class="mt-6">
        <flux:table.columns>
            <flux:table.column>#</flux:table.column>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Role</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Last Login</flux:table.column>
            <flux:table.column>Invited At</flux:table.column>
            <flux:table.column align="center">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($users as $index => $user)
                <flux:table.row>
                    <flux:table.cell>
                        {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">
                        <div class="flex items-center gap-2">
                            <flux:avatar circle size="sm" name="{{ $user->name }}" color="auto" />
                            <span>{{ $user->name }}</span>
                            @if($user->id === auth()->id())
                                <flux:badge size="sm" color="zinc" inset="top bottom">You</flux:badge>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $user->email }}</flux:table.cell>
                    <flux:table.cell>
                        <x-role-badge :role="$user->role" />
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="{{ $user->status->color() }}" inset="top bottom">
                            {{ $user->status->label() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                    </flux:table.cell>
                    <flux:table.cell>{{ $user->invitation ? $user->invitation->created_at->format('d M Y') : '-' }}
                    </flux:table.cell>
                    <flux:table.cell class="text-center">
                        @if($user->trashed())
                            @can('restore', $user)
                                <flux:button size="sm" icon="arrow-path" variant="ghost" wire:click="restore('{{ $user->id }}')">
                                    Restore</flux:button>
                            @endcan
                        @else
                            @canany(['update', 'delete'], $user)
                                <flux:dropdown align="end">
                                    <flux:button size="sm" icon="ellipsis-vertical" variant="ghost" />

                                    <flux:menu>
                                        @can('update', $user)
                                            <flux:menu.item icon="pencil-square" wire:click="edit('{{ $user->id }}')">Edit
                                            </flux:menu.item>

                                            @if($user->status === \App\Enums\UserStatus::PendingInvitation)
                                                <flux:menu.item icon="envelope" wire:click="resendInvitation('{{ $user->id }}')">
                                                    Resend Invitation
                                                </flux:menu.item>
                                            @else
                                                <flux:menu.item icon="key" wire:click="sendPasswordReset('{{ $user->id }}')">
                                                    Send Password Reset
                                                </flux:menu.item>
                                            @endif

                                            @if($user->id !== auth()->id())
                                                @if($user->status !== \App\Enums\UserStatus::PendingInvitation)
                                                    <flux:menu.item
                                                        icon="{{ $user->status === \App\Enums\UserStatus::Active ? 'lock-closed' : 'lock-open' }}"
                                                        wire:click="toggleStatus('{{ $user->id }}')">
                                                        {{ $user->status === \App\Enums\UserStatus::Active ? 'Deactivate User' : 'Activate User' }}
                                                    </flux:menu.item>
                                                @endif
                                            @endif
                                        @endcan
                                        @can('delete', $user)
                                            @if($user->id !== auth()->id())
                                                <flux:menu.item variant="danger" icon="trash" wire:click="confirmDelete('{{ $user->id }}')">
                                                    Delete
                                                </flux:menu.item>
                                            @endif
                                        @endcan
                                    </flux:menu>
                                </flux:dropdown>
                            @endcanany
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center text-zinc-500 py-6">No team members found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    @if($users->hasPages())
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif
</flux:card>